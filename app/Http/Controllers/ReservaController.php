<?php

namespace App\Http\Controllers;

use App\Models\Reserva;
use App\Models\ItemBiblioteca;
use App\Models\User;
use App\Models\PoliticaAcesso;
use App\Models\Emprestimo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Historico;

class ReservaController extends Controller
{
    /**
     * Listar reservas da escola
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }
        
        $reservas = Reserva::with(['item', 'usuario'])
            ->where('escola_id', $escolaId)
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->usuario_id, function ($query, $usuarioId) {
                return $query->where('usuario_id', $usuarioId);
            })
            ->when($request->expiradas, function ($query) {
                return $query->where('expires_at', '<', now())
                           ->where('status', 'ativa');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Calcular posição na fila para cada reserva exibida
        $reservas->setCollection(
            $reservas->getCollection()->map(function ($reserva) {
                $reserva->posicao_fila = $this->calcularPosicaoFila($reserva);
                return $reserva;
            })
        );

        // Usuários ativos da escola para o modal e filtros
        $usuarios = User::where('escola_id', $escolaId)
                       ->where('ativo', true)
                       ->orderBy('name')
                       ->get();

        // Itens para seleção de reserva: listar todos com contagem de empréstimos ativos
        $itensReserva = ItemBiblioteca::where('escola_id', $escolaId)
            ->withCount(['emprestimos as emprestimos_ativos_count' => function ($q) {
                $q->where('status', 'ativo');
            }])
            ->orderBy('titulo')
            ->get();

        return view('biblioteca.reservas.index', compact('reservas', 'usuarios', 'itensReserva'));
    }

    /**
     * Mostrar formulário de nova reserva
     */
    public function create(Request $request)
    {
        // Resolver escola atual: para super admin/suporte, usar session('escola_atual') se disponível
        $user = Auth::user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }
        
        $item = null;
        if ($request->item_id) {
            $item = ItemBiblioteca::where('escola_id', $escolaId)
                                 ->where('id', $request->item_id)
                                 ->first();
        }

        $usuarios = User::where('escola_id', $escolaId)
                       ->where('ativo', true)
                       ->orderBy('name')
                       ->get();

        return view('biblioteca.reservas.create', compact('item', 'usuarios'));
    }

    /**
     * Criar reserva
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:item_biblioteca,id',
            'usuario_id' => 'required|exists:users,id'
        ]);

        // Resolver escola atual: para super admin/suporte, usar session('escola_atual') se disponível
        $user = Auth::user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }
        
        // Verificar se o item pertence à escola (evitar ModelNotFound)
        $item = ItemBiblioteca::where('escola_id', $escolaId)
                             ->where('id', $request->item_id)
                             ->first();
        if (!$item) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item não encontrado na escola atual.',
                    'errors' => ['item_id' => ['Item não encontrado na escola atual.']]
                ], 422);
            }
            return back()->withErrors(['item_id' => 'Item não encontrado na escola atual.'])->withInput();
        }

        // Verificar se o usuário pertence à escola (evitar ModelNotFound)
        $usuario = User::where('escola_id', $escolaId)
                      ->where('id', $request->usuario_id)
                      ->first();
        if (!$usuario) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não pertence à escola atual.',
                    'errors' => ['usuario_id' => ['Usuário não pertence à escola atual.']]
                ], 422);
            }
            return back()->withErrors(['usuario_id' => 'Usuário não pertence à escola atual.'])->withInput();
        }

        // Verificar se já existe reserva ativa do usuário para este item
        $reservaExistente = Reserva::where('escola_id', $escolaId)
                                  ->where('item_id', $item->id)
                                  ->where('usuario_id', $usuario->id)
                                  ->where('status', 'ativa')
                                  ->exists();

        if ($reservaExistente) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário já possui reserva ativa para este item.',
                    'errors' => ['item_id' => ['Usuário já possui reserva ativa para este item.']]
                ], 422);
            }
            return back()->withErrors(['item_id' => 'Usuário já possui reserva ativa para este item.']);
        }

        // Verificar se o item está disponível (se sim, permitir empréstimo direto sob confirmação)
        if ($this->verificarDisponibilidadeImediata($item)) {
            // Se o frontend confirmar, criar empréstimo automaticamente em vez de reserva
            if ($request->boolean('auto_emprestimo')) {
                // Políticas de empréstimo
                $violacao = $this->verificarPoliticasAcessoEmprestimo($usuario, $item);
                if ($violacao) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => $violacao,
                            'errors' => ['usuario_id' => [$violacao]]
                        ], 422);
                    }
                    return back()->withErrors(['usuario_id' => $violacao]);
                }

                // Capacidade disponível
                $emprestimosAtivosItem = Emprestimo::where('escola_id', $escolaId)
                    ->where('item_id', $item->id)
                    ->where('status', 'ativo')
                    ->count();
                if ($emprestimosAtivosItem >= ($item->quantidade_fisica ?? 0)) {
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Não há unidades disponíveis para empréstimo.',
                        ], 422);
                    }
                    return back()->withErrors(['item_id' => 'Não há unidades disponíveis para empréstimo.']);
                }

                // Prazo e criação do empréstimo
                $prazoDias = $this->obterPrazoEmprestimo($usuario, $item);
                $dataPrevista = Carbon::now()->addDays($prazoDias);

                DB::beginTransaction();
                try {
                    $emprestimo = Emprestimo::create([
                        'escola_id' => $escolaId,
                        'item_id' => $item->id,
                        'usuario_id' => $usuario->id,
                        'data_emprestimo' => now(),
                        'data_prevista' => $dataPrevista,
                        'status' => 'ativo',
                    ]);
                DB::commit();
                Historico::registrar('criado', 'Emprestimo', $emprestimo->id, null, $emprestimo->toArray());

                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Empréstimo criado automaticamente (item disponível).',
                            'emprestimo_id' => $emprestimo->id,
                        ]);
                    }
                    return redirect()->route('biblioteca.emprestimos.show', $emprestimo->id)
                        ->with('success', 'Empréstimo criado automaticamente (item disponível).');
                } catch (\Exception $e) {
                    DB::rollBack();
                    if ($request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Falha ao criar empréstimo automaticamente.',
                        ], 500);
                    }
                    return back()->withErrors(['error' => 'Falha ao criar empréstimo automaticamente.']);
                }
            }

            // Caso não confirmado, orientar a realizar empréstimo direto
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item está disponível. Realize o empréstimo diretamente.',
                    'errors' => ['item_id' => ['Item está disponível. Realize o empréstimo diretamente.']]
                ], 422);
            }
            return back()->withErrors(['item_id' => 'Item está disponível. Realize o empréstimo diretamente.']);
        }

        // Verificar políticas de acesso
        $politicaViolada = $this->verificarPoliticasAcesso($usuario, $item);
        if ($politicaViolada) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $politicaViolada,
                    'errors' => ['usuario_id' => [$politicaViolada]]
                ], 422);
            }
            return back()->withErrors(['usuario_id' => $politicaViolada]);
        }

        DB::beginTransaction();
        try {
            // Calcular prioridade e data de expiração
            $prioridade = $this->calcularPrioridade($usuario);
            $dataExpiracao = Carbon::now()->addDays(7); // 7 dias para retirar

            // Criar reserva
            $reserva = Reserva::create([
                'escola_id' => $escolaId,
                'item_id' => $item->id,
                'usuario_id' => $usuario->id,
                'data_reserva' => now(),
                'expires_at' => $dataExpiracao,
                'status' => 'ativa',
                'prioridade' => $prioridade
            ]);
            Historico::registrar('criado', 'Reserva', $reserva->id, null, $reserva->toArray());

            DB::commit();

            // Responder em JSON para AJAX
            if ($request->expectsJson()) {
                // Recarregar reservas para atualizar a listagem (usar mesma resolução de escola)
                $escolaId = session('escola_atual') ?: Auth::user()->escola_id;
                $reservas = Reserva::with(['item', 'usuario'])
                    ->where('escola_id', $escolaId)
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);

                // Calcular posição na fila para cada reserva exibida
                $reservas->setCollection(
                    $reservas->getCollection()->map(function ($r) {
                        $r->posicao_fila = $this->calcularPosicaoFila($r);
                        return $r;
                    })
                );

                $htmlList = view('biblioteca.reservas._list', compact('reservas'))->render();

                return response()->json([
                    'success' => true,
                    'message' => 'Reserva realizada com sucesso!',
                    'reserva_id' => $reserva->id,
                    'html_list' => $htmlList,
                ]);
            }

            return redirect()->route('biblioteca.reservas.show', $reserva)
                           ->with('success', 'Reserva realizada com sucesso!');

        } catch (\Exception $e) {
            DB::rollback();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao realizar reserva: ' . $e->getMessage(),
                ], 422);
            }
            return back()->withErrors(['error' => 'Erro ao realizar reserva: ' . $e->getMessage()]);
        }
    }

    /**
     * Mostrar detalhes da reserva
     */
    public function show(Reserva $reserva)
    {
        $this->authorize('view', $reserva);
        
        $reserva->load(['item', 'usuario']);
        
        // Calcular posição na fila
        $posicaoFila = $this->calcularPosicaoFila($reserva);
        
        return view('biblioteca.reservas.show', compact('reserva', 'posicaoFila'));
    }

    /**
     * Cancelar reserva
     */
    public function cancelar(Reserva $reserva)
    {
        $this->authorize('update', $reserva);

        if ($reserva->status !== 'ativa') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta reserva não está ativa.'
                ], 422);
            }
            return back()->withErrors(['error' => 'Esta reserva não está ativa.']);
        }

        $updateData = [
            'status' => 'cancelada',
            'data_cancelamento' => now()
        ];

        $dadosAntigos = $reserva->toArray();
        $reserva->update($updateData);
        $dadosNovos = $reserva->fresh()->toArray();
        Historico::registrar('inativado', 'Reserva', $reserva->id, $dadosAntigos, $dadosNovos);

        if (request()->expectsJson()) {
            // Usar escola atual resolvida por sessão quando disponível
            $escolaId = session('escola_atual') ?: Auth::user()->escola_id;
            $reservas = Reserva::with(['item', 'usuario'])
                ->where('escola_id', $escolaId)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            // Calcular posição na fila para cada reserva exibida
            $reservas->setCollection(
                $reservas->getCollection()->map(function ($r) {
                    $r->posicao_fila = $this->calcularPosicaoFila($r);
                    return $r;
                })
            );

            $htmlList = view('biblioteca.reservas._list', compact('reservas'))->render();

            return response()->json([
                'success' => true,
                'message' => 'Reserva cancelada com sucesso!',
                'html_list' => $htmlList,
            ]);
        }

        return redirect()->route('biblioteca.reservas.index')
                       ->with('success', 'Reserva cancelada com sucesso!');
    }

    /**
     * Processar reserva (quando item fica disponível)
     */
    public function processar(Reserva $reserva)
    {
        $this->authorize('update', $reserva);

        if ($reserva->status !== 'ativa') {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta reserva não está ativa.'
                ], 422);
            }
            return back()->withErrors(['error' => 'Esta reserva não está ativa.']);
        }

        // Verificar se o item está disponível
        if (!$this->verificarDisponibilidadeImediata($reserva->item)) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item ainda não está disponível.'
                ], 422);
            }
            return back()->withErrors(['error' => 'Item ainda não está disponível.']);
        }

        // Se modo manual foi solicitado, retornar redirect_url para o formulário de empréstimo
        $modo = request()->string('modo');
        if ($modo === 'manual') {
            $redirectUrl = route('biblioteca.emprestimos.create', ['item_id' => $reserva->item_id]);
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'redirect_url' => $redirectUrl,
                ]);
            }
            return redirect($redirectUrl);
        }

        // Criação automática de empréstimo e atualização da reserva
        $user = Auth::user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }

        // Validar contexto da escola para item e usuário
        $item = ItemBiblioteca::where('escola_id', $escolaId)
            ->where('id', $reserva->item_id)
            ->first();
        $usuario = User::where('escola_id', $escolaId)
            ->where('id', $reserva->usuario_id)
            ->first();

        if (!$item || !$usuario) {
            $msg = 'Item ou usuário não pertencem à escola atual.';
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->withErrors(['error' => $msg]);
        }

        // Políticas de empréstimo
        $violacao = $this->verificarPoliticasAcessoEmprestimo($usuario, $item);
        if ($violacao) {
            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $violacao], 422);
            }
            return back()->withErrors(['error' => $violacao]);
        }

        DB::beginTransaction();
        try {
            // Data prevista conforme política
            $prazoDias = $this->obterPrazoEmprestimo($usuario, $item);
            $dataPrevista = Carbon::now()->addDays($prazoDias);

            // Criar empréstimo
            $emprestimo = \App\Models\Emprestimo::create([
                'escola_id' => $escolaId,
                'item_id' => $item->id,
                'usuario_id' => $usuario->id,
                'data_emprestimo' => now(),
                'data_prevista' => $dataPrevista,
                'status' => 'ativo',
            ]);

            // Atualizar reserva para 'processada'
            $dadosAntigosReserva = $reserva->toArray();
            $reserva->update(['status' => 'processada']);
            Historico::registrar('criado', 'Emprestimo', $emprestimo->id, null, $emprestimo->toArray());
            Historico::registrar('atualizado', 'Reserva', $reserva->id, $dadosAntigosReserva, $reserva->fresh()->toArray());

            DB::commit();

            if (request()->expectsJson()) {
                $reservas = Reserva::with(['item', 'usuario'])
                    ->where('escola_id', $escolaId)
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);
                $reservas->setCollection(
                    $reservas->getCollection()->map(function ($r) {
                        $r->posicao_fila = $this->calcularPosicaoFila($r);
                        return $r;
                    })
                );
                $htmlList = view('biblioteca.reservas._list', compact('reservas'))->render();

                return response()->json([
                    'success' => true,
                    'message' => 'Empréstimo criado automaticamente e reserva processada.',
                    'emprestimo_id' => $emprestimo->id,
                    'html_list' => $htmlList,
                ]);
            }

            return redirect()->route('biblioteca.emprestimos.show', $emprestimo->id)
                ->with('success', 'Empréstimo criado automaticamente e reserva processada.');
        } catch (\Exception $e) {
            DB::rollBack();
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao criar empréstimo: ' . $e->getMessage(),
                ], 422);
            }
            return back()->withErrors(['error' => 'Erro ao criar empréstimo: ' . $e->getMessage()]);
        }
    }

    /**
     * Listar reservas do usuário logado
     */
    public function minhasReservas()
    {
        $user = Auth::user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }
        
        $reservas = Reserva::with(['item'])
            ->where('escola_id', $escolaId)
            ->where('usuario_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('biblioteca.reservas.minhas', compact('reservas'));
    }

    /**
     * Verificar disponibilidade imediata do item
     */
    private function verificarDisponibilidadeImediata(ItemBiblioteca $item): bool
    {
        // Item precisa estar habilitado para empréstimo e em um status utilizável
        if (!$item->habilitado_emprestimo) {
            return false;
        }

        if (!in_array($item->status, ['disponivel', 'ativo'])) {
            return false;
        }

        // Disponibilidade baseada na quantidade física menos empréstimos ativos
        $emprestimosAtivos = $item->emprestimos()->where('status', 'ativo')->count();
        return $emprestimosAtivos < $item->quantidade_fisica;
    }

    /**
     * Verificar políticas de acesso para reservas
     */
    private function verificarPoliticasAcesso(User $usuario, ItemBiblioteca $item): ?string
    {
        // Buscar política para o perfil do usuário e tipo do item
        $politica = PoliticaAcesso::where('escola_id', $usuario->escola_id)
                                 ->where('perfil', $this->obterPerfilUsuario($usuario))
                                 ->where('tipo_item', $item->tipo)
                                 ->first();

        if (!$politica) {
            return null; // Sem restrições específicas
        }

        // Verificar limite de reservas ativas
        $reservasAtivas = Reserva::where('escola_id', $usuario->escola_id)
                                ->where('usuario_id', $usuario->id)
                                ->where('status', 'ativa')
                                ->count();

        if ($reservasAtivas >= $politica->max_reservas) {
            return "Usuário atingiu o limite de {$politica->max_reservas} reservas simultâneas.";
        }

        return null;
    }

    /**
     * Verificar políticas de acesso para empréstimos (espelho do EmprestimoController)
     */
    private function verificarPoliticasAcessoEmprestimo(User $usuario, ItemBiblioteca $item): ?string
    {
        $perfil = $this->obterPerfilUsuario($usuario);
        $escolaId = $item->escola_id;

        $politicaGlobal = \App\Models\BibliotecaPolitica::where('escola_id', $escolaId)->first();

        if ($politicaGlobal) {
            if ($perfil === 'aluno' && !$politicaGlobal->permitir_alunos) {
                return 'Alunos não estão habilitados a realizar empréstimos.';
            }
            if ($perfil === 'funcionario' && !$politicaGlobal->permitir_funcionarios) {
                return 'Funcionários não estão habilitados a realizar empréstimos.';
            }

            if ($politicaGlobal->bloquear_por_multas) {
                $atrasosPendentes = \App\Models\Emprestimo::where('escola_id', $escolaId)
                    ->where('usuario_id', $usuario->id)
                    ->where('status', 'ativo')
                    ->where('data_prevista', '<', now())
                    ->count();
                if ($atrasosPendentes > 0) {
                    return 'Empréstimo bloqueado: existem empréstimos em atraso pendentes.';
                }
            }
        }

        $politicaPerfilTipo = PoliticaAcesso::where('escola_id', $escolaId)
            ->where('perfil', $perfil)
            ->where('tipo_item', $item->tipo)
            ->first();

        $emprestimosAtivos = \App\Models\Emprestimo::where('escola_id', $escolaId)
            ->where('usuario_id', $usuario->id)
            ->where('status', 'ativo')
            ->count();

        if ($politicaGlobal && $politicaGlobal->max_emprestimos_por_usuario !== null) {
            if ($emprestimosAtivos >= $politicaGlobal->max_emprestimos_por_usuario) {
                return "Usuário atingiu o limite de {$politicaGlobal->max_emprestimos_por_usuario} empréstimos simultâneos (política da escola).";
            }
        }

        if ($politicaPerfilTipo && $politicaPerfilTipo->max_emprestimos !== null) {
            if ($emprestimosAtivos >= $politicaPerfilTipo->max_emprestimos) {
                return "Usuário atingiu o limite de {$politicaPerfilTipo->max_emprestimos} empréstimos simultâneos (perfil/tipo).";
            }
        }

        return null;
    }

    /**
     * Obter prazo de empréstimo baseado na política (espelho do EmprestimoController)
     */
    private function obterPrazoEmprestimo(User $usuario, ItemBiblioteca $item): int
    {
        $escolaId = $item->escola_id;
        $politica = PoliticaAcesso::where('escola_id', $escolaId)
            ->where('perfil', $this->obterPerfilUsuario($usuario))
            ->where('tipo_item', $item->tipo)
            ->first();

        if ($politica && $politica->prazo_dias) {
            return (int) $politica->prazo_dias;
        }

        $global = \App\Models\BibliotecaPolitica::where('escola_id', $escolaId)->first();
        return $global && $global->prazo_padrao_dias ? (int) $global->prazo_padrao_dias : 7;
    }

    /**
     * Calcular prioridade da reserva baseada no perfil do usuário
     */
    private function calcularPrioridade(User $usuario): int
    {
        $cargos = $usuario->cargos->pluck('nome')->toArray();
        
        // Professores têm prioridade alta
        if (in_array('Professor', $cargos)) {
            return 1;
        }
        
        // Funcionários têm prioridade média
        if (in_array('Funcionário', $cargos)) {
            return 2;
        }
        
        // Alunos têm prioridade normal
        return 3;
    }

    /**
     * Calcular posição na fila de reservas
     */
    private function calcularPosicaoFila(Reserva $reserva): int
    {
        return Reserva::where('item_id', $reserva->item_id)
                     ->where('status', 'ativa')
                     ->where(function ($query) use ($reserva) {
                         $query->where('prioridade', '<', $reserva->prioridade)
                               ->orWhere(function ($q) use ($reserva) {
                                   $q->where('prioridade', $reserva->prioridade)
                                     ->where('created_at', '<', $reserva->created_at);
                               });
                     })
                     ->count() + 1;
    }

    /**
     * Obter perfil do usuário para políticas
     */
    private function obterPerfilUsuario(User $usuario): string
    {
        // Lógica para determinar o perfil baseado nos cargos do usuário
        $cargos = $usuario->cargos->pluck('nome')->toArray();
        
        if (in_array('Professor', $cargos)) {
            return 'professor';
        } elseif (in_array('Aluno', $cargos)) {
            return 'aluno';
        } elseif (in_array('Funcionário', $cargos)) {
            return 'funcionario';
        }
        
        return 'geral';
    }

    /**
     * Processar reservas expiradas (comando/job)
     */
    public function processarExpiradas()
    {
        $reservasExpiradas = Reserva::where('status', 'ativa')
                                   ->where('expires_at', '<', now())
                                   ->get();

        foreach ($reservasExpiradas as $reserva) {
            $dadosAntigos = $reserva->toArray();
            $reserva->update(['status' => 'expirada']);
            Historico::registrar('inativado', 'Reserva', $reserva->id, $dadosAntigos, $reserva->fresh()->toArray());
        }

        return response()->json([
            'message' => 'Reservas expiradas processadas',
            'count' => $reservasExpiradas->count()
        ]);
    }
}
