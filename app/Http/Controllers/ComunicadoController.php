<?php

namespace App\Http\Controllers;

use App\Models\Comunicado;
use App\Models\Turma;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Notification;
use App\Models\Historico;

class ComunicadoController extends Controller
{
    /**
     * Listar comunicados
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $status = $request->get('status', 'publicados'); // 'publicados' ou 'rascunhos'
        
        $query = Comunicado::with(['autor', 'turma'])
            ->ativas();
            
        // Filtro por status (publicados ou rascunhos)
        if ($status === 'rascunhos') {
            $query->whereNull('publicado_em');
        } else {
            $query->publicados();
        }
            
        // O filtro por escola é feito automaticamente pelo global scope do modelo
        // através da relação com o autor (User)

        // Filtrar por tipo se especificado
        if ($request->filled('tipo')) {
            $query->porTipo($request->tipo);
        }

        // Filtrar por destinatário baseado no perfil do usuário
        if ($user->hasRole('responsavel')) {
            $query->where(function ($q) use ($user) {
                $q->porDestinatario('todos')
                  ->orWhere('destinatario_tipo', 'pais')
                  ->orWhere(function ($subQ) use ($user) {
                      $subQ->where('destinatario_tipo', 'turma_especifica')
                           ->whereHas('turma.alunos.responsaveis', function ($turmaQ) use ($user) {
                               $turmaQ->where('user_id', $user->id);
                           });
                  });
            });
        } elseif ($user->hasRole('professor')) {
            $query->where(function ($q) {
                $q->porDestinatario('todos')
                  ->orWhere('destinatario_tipo', 'professores');
            });
        }

        $comunicados = $query->orderBy('created_at', 'desc')
            ->paginate(15);

        // Verificar confirmações para comunicados que requerem
        foreach ($comunicados as $comunicado) {
            if ($comunicado->requer_confirmacao) {
                $comunicado->foi_confirmado = $comunicado->foiConfirmadoPor($user->id);
            }
        }

        $tipos = ['informativo', 'urgente', 'evento', 'reuniao', 'aviso'];
        // Turmas para uso no modal de criação considerando escola atual (super admin/suporte)
        $escolaIdTurmas = session('escola_atual') ?: auth()->user()->escola_id;
        $turmas = Turma::where('escola_id', $escolaIdTurmas)->ativas()->orderBy('nome')->get();

        return view('comunicacao.comunicados.index', compact('comunicados', 'tipos', 'status', 'turmas'));
    }

    /**
     * Exibir comunicado específico
     */
    public function show(Comunicado $comunicado): View
    {
        $user = Auth::user();
        
        // Verificar se o usuário pode ver este comunicado
        if (!$this->podeVerComunicado($comunicado, $user)) {
            abort(403, 'Você não tem acesso a este comunicado.');
        }

        $comunicado->load(['autor', 'turma', 'confirmacoes.usuario']);
        
        $foiConfirmado = $comunicado->requer_confirmacao ? 
            $comunicado->foiConfirmadoPor($user->id) : false;

        return view('comunicacao.comunicados.show', compact('comunicado', 'foiConfirmado'));
    }

    /**
     * Criar novo comunicado (apenas admin/coordenador)
     */
    public function create(): View
    {
        $this->authorize('create', Comunicado::class);
        
        $escolaIdTurmas = session('escola_atual') ?: auth()->user()->escola_id;
        $turmas = Turma::where('escola_id', $escolaIdTurmas)->ativas()->orderBy('nome')->get();
        $tipos = ['informativo', 'urgente', 'evento', 'reuniao', 'aviso'];
        $destinatarios = ['todos', 'pais', 'professores', 'turma_especifica'];

        return view('comunicacao.comunicados.create', compact('turmas', 'tipos', 'destinatarios'));
    }

    /**
     * Armazenar novo comunicado
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Comunicado::class);
        
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'conteudo' => 'required|string',
            'tipo' => 'required|in:informativo,urgente,evento,reuniao,aviso',
            'destinatario_tipo' => 'required|in:todos,pais,professores,turma_especifica',
            'turma_id' => 'nullable|required_if:destinatario_tipo,turma_especifica|exists:turmas,id',
            'requer_confirmacao' => 'boolean',
            'data_evento' => 'nullable|date|after_or_equal:today',
            'hora_evento' => 'nullable|date_format:H:i',
            'local_evento' => 'nullable|string|max:255',
            'publicar_agora' => 'boolean'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $dadosComunicado = [
                'titulo' => $request->titulo,
                'conteudo' => $request->conteudo,
                'tipo' => $request->tipo,
                'destinatario_tipo' => $request->destinatario_tipo,
                'turma_id' => $request->turma_id,
                'autor_id' => Auth::id(),
                'escola_id' => session('escola_atual') ?: auth()->user()->escola_id,
                'requer_confirmacao' => $request->boolean('requer_confirmacao'),
                'data_evento' => $request->data_evento,
                'hora_evento' => $request->hora_evento ? Carbon::createFromFormat('H:i', $request->hora_evento) : null,
                'local_evento' => $request->local_evento,
                'ativo' => true
            ];

            if ($request->boolean('publicar_agora')) {
                $dadosComunicado['publicado_em'] = now();
            }

            $comunicado = Comunicado::create($dadosComunicado);
            Historico::registrar('criado', 'Comunicado', $comunicado->id, null, $comunicado->toArray());

            // Criar notificação se o comunicado foi publicado
            if ($request->boolean('publicar_agora')) {
                $this->criarNotificacaoComunicado($comunicado);
            }

            $mensagem = $request->boolean('publicar_agora') ? 
                'Comunicado publicado com sucesso!' : 
                'Comunicado criado com sucesso! Você pode publicá-lo quando desejar.';

            if ($request->expectsJson() || $request->ajax()) {
                $comunicado->load(['autor', 'turma']);
                // Anexar atributos computados
                $comunicado->append(['icone_tipo', 'classe_tipo', 'data_hora_evento_formatada']);
                return response()->json([
                    'success' => true,
                    'message' => $mensagem,
                    'comunicado' => $comunicado
                ]);
            }

            return redirect()->route('comunicados.show', $comunicado)
                ->with('success', $mensagem);

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao criar comunicado: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()
                ->with('error', 'Erro ao criar comunicado: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Editar comunicado
     */
    public function edit(Comunicado $comunicado): View
    {
        $this->authorize('update', $comunicado);
        
        $escolaIdTurmas = session('escola_atual') ?: auth()->user()->escola_id;
        $turmas = Turma::where('escola_id', $escolaIdTurmas)->ativas()->orderBy('nome')->get();
        $tipos = ['informativo', 'urgente', 'evento', 'reuniao', 'aviso'];
        $destinatarios = ['todos', 'pais', 'professores', 'turma_especifica'];

        return view('comunicacao.comunicados.edit', compact('comunicado', 'turmas', 'tipos', 'destinatarios'));
    }

    /**
     * Atualizar comunicado
     */
    public function update(Request $request, Comunicado $comunicado): RedirectResponse
    {
        $this->authorize('update', $comunicado);
        
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'conteudo' => 'required|string',
            'tipo' => 'required|in:informativo,urgente,evento,reuniao,aviso',
            'destinatario_tipo' => 'required|in:todos,pais,professores,turma_especifica',
            'turma_id' => 'nullable|required_if:destinatario_tipo,turma_especifica|exists:turmas,id',
            'requer_confirmacao' => 'boolean',
            'data_evento' => 'nullable|date',
            'hora_evento' => 'nullable|date_format:H:i',
            'local_evento' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $dadosAntigos = $comunicado->toArray();
            $comunicado->update([
                'titulo' => $request->titulo,
                'conteudo' => $request->conteudo,
                'tipo' => $request->tipo,
                'destinatario_tipo' => $request->destinatario_tipo,
                'turma_id' => $request->turma_id,
                'requer_confirmacao' => $request->boolean('requer_confirmacao'),
                'data_evento' => $request->data_evento,
                'hora_evento' => $request->hora_evento ? Carbon::createFromFormat('H:i', $request->hora_evento) : null,
                'local_evento' => $request->local_evento
            ]);
            $dadosNovos = $comunicado->fresh()->toArray();
            Historico::registrar('atualizado', 'Comunicado', $comunicado->id, $dadosAntigos, $dadosNovos);

            return redirect()->route('comunicados.show', $comunicado)
                ->with('success', 'Comunicado atualizado com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao atualizar comunicado: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Publicar comunicado
     */
    public function publicar(Comunicado $comunicado): JsonResponse
    {
        $this->authorize('update', $comunicado);
        
        try {
            $dadosAntigos = $comunicado->toArray();
            $comunicado->publicar();
            $dadosNovos = $comunicado->fresh()->toArray();
            Historico::registrar('ativado', 'Comunicado', $comunicado->id, $dadosAntigos, $dadosNovos);
            // Disparar notificações após publicar
            $this->criarNotificacaoComunicado($comunicado);
            
            return response()->json([
                'success' => true,
                'message' => 'Comunicado publicado e notificações enviadas com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao publicar comunicado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Despublicar comunicado
     */
    public function despublicar(Comunicado $comunicado): JsonResponse
    {
        $this->authorize('update', $comunicado);
        
        try {
            $dadosAntigos = $comunicado->toArray();
            $comunicado->despublicar();
            $dadosNovos = $comunicado->fresh()->toArray();
            Historico::registrar('inativado', 'Comunicado', $comunicado->id, $dadosAntigos, $dadosNovos);
            
            return response()->json([
                'success' => true,
                'message' => 'Comunicado despublicado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao despublicar comunicado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirmar comunicado
     */
    public function confirmar(Request $request, Comunicado $comunicado): JsonResponse
    {
        $user = Auth::user();
        
        if (!$comunicado->requer_confirmacao) {
            return response()->json([
                'success' => false,
                'message' => 'Este comunicado não requer confirmação.'
            ], 400);
        }

        if (!$this->podeVerComunicado($comunicado, $user)) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem acesso a este comunicado.'
            ], 403);
        }

        try {
            $comunicado->confirmarPor($user->id, $request->observacoes);
            
            return response()->json([
                'success' => true,
                'message' => 'Comunicado confirmado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao confirmar comunicado: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Relatório de confirmações
     */
    public function relatorioConfirmacoes(Comunicado $comunicado): View
    {
        $this->authorize('view', $comunicado);
        
        if (!$comunicado->requer_confirmacao) {
            abort(404, 'Este comunicado não requer confirmação.');
        }

        $comunicado->load(['confirmacoes.usuario', 'turma']);
        
        $totalDestinatarios = $comunicado->contarDestinatarios();
        $totalConfirmacoes = $comunicado->contarConfirmacoes();
        $porcentagemConfirmacoes = $comunicado->porcentagemConfirmacoes();

        return view('comunicacao.comunicados.relatorio-confirmacoes', compact(
            'comunicado', 
            'totalDestinatarios', 
            'totalConfirmacoes', 
            'porcentagemConfirmacoes'
        ));
    }

    /**
     * Deletar comunicado
     */
    public function destroy(Comunicado $comunicado): RedirectResponse
    {
        $this->authorize('delete', $comunicado);
        
        try {
            $dadosAntigos = $comunicado->toArray();
            $comunicado->delete();
            Historico::registrar('excluido', 'Comunicado', $comunicado->id, $dadosAntigos, null);
            
            return redirect()->route('comunicados.index')
                ->with('success', 'Comunicado deletado com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao deletar comunicado: ' . $e->getMessage());
        }
    }

    /**
     * Verificar se o usuário pode ver o comunicado
     */
    private function podeVerComunicado(Comunicado $comunicado, User $user): bool
    {
        // Admin pode ver todos
        if ($user->hasRole('admin')) {
            return true;
        }

        // Autor pode ver seus próprios comunicados
        if ($comunicado->autor_id === $user->id) {
            return true;
        }

        // Verificar se está publicado
        if (!$comunicado->isPublicado()) {
            return false;
        }

        // Verificar destinatário
        switch ($comunicado->destinatario_tipo) {
            case 'todos':
                return true;
            case 'pais':
                return $user->hasRole('responsavel');
            case 'professores':
                return $user->hasRole('professor');
            case 'turma_especifica':
                if ($user->hasRole('responsavel')) {
                    return $user->responsaveis()
                        ->whereHas('alunos.turma', function ($query) use ($comunicado) {
                            $query->where('id', $comunicado->turma_id);
                        })->exists();
                }
                return false;
            default:
                return false;
        }
    }

    /**
     * Criar notificação para comunicado publicado
     */
    private function criarNotificacaoComunicado(Comunicado $comunicado): void
    {
        try {
            // Determinar o tipo de notificação baseado no tipo do comunicado
            $tipoNotificacao = match($comunicado->tipo) {
                'urgente' => 'warning',
                'evento', 'reuniao' => 'info',
                default => 'info'
            };

            // Criar título da notificação
            $titulo = match($comunicado->tipo) {
                'urgente' => '🚨 Comunicado Urgente',
                'evento' => '📅 Novo Evento',
                'reuniao' => '👥 Nova Reunião',
                'aviso' => '⚠️ Novo Aviso',
                default => '📢 Novo Comunicado'
            };

            // Criar mensagem da notificação
            $mensagem = $comunicado->titulo;
            if ($comunicado->isEvento() && $comunicado->data_hora_evento_formatada) {
                $mensagem .= ' - ' . $comunicado->data_hora_evento_formatada;
            }

            // URL de ação
            $actionUrl = route('comunicados.show', $comunicado);
            $actionText = 'Ver Comunicado';

            // Criar notificação baseada no destinatário
            switch ($comunicado->destinatario_tipo) {
                case 'todos':
                    // Notificação para todos os usuários da escola atual
                    $escolaId = \App\Http\Middleware\EscolaContext::getEscolaAtual() ?: (auth()->user()->escola_id ?? $comunicado->escola_id);
                    $usuarios = \App\Models\User::where('escola_id', $escolaId)->get();
                    foreach ($usuarios as $usuario) {
                        Notification::createForUser(
                            $usuario->id,
                            $tipoNotificacao,
                            $titulo,
                            $mensagem,
                            ['comunicado_id' => $comunicado->id],
                            $actionUrl,
                            $actionText
                        );
                    }
                    break;

                case 'pais':
                    // Notificação para responsáveis apenas da escola atual
                    $escolaId = \App\Http\Middleware\EscolaContext::getEscolaAtual() ?: (auth()->user()->escola_id ?? $comunicado->escola_id);
                    $responsaveis = \App\Models\User::where('escola_id', $escolaId)->whereHas('responsaveis')->get();
                    foreach ($responsaveis as $responsavel) {
                        Notification::createForUser(
                            $responsavel->id,
                            $tipoNotificacao,
                            $titulo,
                            $mensagem,
                            ['comunicado_id' => $comunicado->id],
                            $actionUrl,
                            $actionText
                        );
                    }
                    break;

                case 'professores':
                    // Notificação para professores apenas da escola atual
                    $escolaId = \App\Http\Middleware\EscolaContext::getEscolaAtual() ?: (auth()->user()->escola_id ?? $comunicado->escola_id);
                    $professores = \App\Models\User::where('escola_id', $escolaId)->whereHas('funcionario')->get();
                    foreach ($professores as $professor) {
                        Notification::createForUser(
                            $professor->id,
                            $tipoNotificacao,
                            $titulo,
                            $mensagem,
                            ['comunicado_id' => $comunicado->id],
                            $actionUrl,
                            $actionText
                        );
                    }
                    break;

                case 'turma_especifica':
                    if ($comunicado->turma_id) {
                        // Notificação para responsáveis dos alunos da turma específica
                        $escolaId = \App\Http\Middleware\EscolaContext::getEscolaAtual() ?: (auth()->user()->escola_id ?? $comunicado->escola_id);
                        $responsaveisTurma = \App\Models\User::where('escola_id', $escolaId)
                            ->whereHas('responsaveis.alunos.turma', function ($query) use ($comunicado) {
                                $query->where('id', $comunicado->turma_id);
                            })->get();
                        
                        foreach ($responsaveisTurma as $responsavel) {
                            Notification::createForUser(
                                $responsavel->id,
                                $tipoNotificacao,
                                $titulo,
                                $mensagem,
                                ['comunicado_id' => $comunicado->id, 'turma_id' => $comunicado->turma_id],
                                $actionUrl,
                                $actionText
                            );
                        }
                    }
                    break;
            }

            \Illuminate\Support\Facades\Log::info('Notificações criadas para comunicado', [
                'comunicado_id' => $comunicado->id,
                'tipo' => $comunicado->tipo,
                'destinatario_tipo' => $comunicado->destinatario_tipo
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Erro ao criar notificações para comunicado', [
                'comunicado_id' => $comunicado->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
