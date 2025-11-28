<?php

namespace App\Http\Controllers;

use App\Models\Emprestimo;
use App\Models\ItemBiblioteca;
use App\Models\User;
use App\Models\PoliticaAcesso;
use App\Models\BibliotecaPolitica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Historico;

class EmprestimoController extends Controller
{
    /**
     * Listar empréstimos da escola
     */
    public function index(Request $request)
    {
        // Determinar escola considerando perfil do usuário
        $user = Auth::user();
        if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: $user->escola_id;
        } else {
            $escolaId = $user->escola_id;
        }

        $emprestimos = Emprestimo::with(['item', 'usuario'])
            ->where('escola_id', $escolaId)
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->usuario_id, function ($query, $usuarioId) {
                return $query->where('usuario_id', $usuarioId);
            })
            ->when($request->vencidos, function ($query) {
                return $query->where('data_prevista', '<', now())
                           ->where('status', 'ativo');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Necessário para o filtro por usuário na view (inclui usuários vinculados via pivot user_escola)
        $usuarios = \App\Models\User::where('ativo', true)
                       ->where(function ($q) use ($escolaId) {
                           $q->where('escola_id', $escolaId)
                             ->orWhereHas('escolas', function ($q2) use ($escolaId) {
                                 $q2->where('escola_id', $escolaId);
                             });
                       })
                       ->orderBy('name')
                       ->get();

        // Lista de itens disponíveis para auxiliar filtros/ações na view
        // Itens elegíveis para empréstimo: considerar 'disponivel' e 'ativo'.
        // A verificação final de disponibilidade usa quantidade física versus empréstimos ativos.
        $itensDisponiveis = \App\Models\ItemBiblioteca::where('escola_id', $escolaId)
                            ->where('habilitado_emprestimo', true)
                            ->whereIn('status', ['disponivel', 'ativo'])
                            ->orderBy('id', 'asc')
                            ->get();

        // Listas para o modal (alunos e funcionários da escola)
        $alunos = \App\Models\Aluno::where('escola_id', $escolaId)
                    ->where('ativo', true)
                    ->orderBy('nome')
                    ->get();

        $funcionarios = \App\Models\Funcionario::where('escola_id', $escolaId)
                          ->where('ativo', true)
                          ->orderBy('nome')
                          ->get();

        return view('biblioteca.emprestimos.index', compact('emprestimos', 'usuarios', 'itensDisponiveis', 'alunos', 'funcionarios'));
    }

    /**
     * Mostrar formulário de novo empréstimo
     */
    public function create(Request $request)
    {
        $escolaId = Auth::user()->escola_id;

        $item = null;
        if ($request->item_id) {
            $item = ItemBiblioteca::where('escola_id', $escolaId)
                                 ->where('id', $request->item_id)
                                 ->first();
        }

        // Coleções usadas no formulário
        $alunos = \App\Models\Aluno::where('escola_id', $escolaId)
                    ->where('ativo', true)
                    ->orderBy('nome')
                    ->get();

        $funcionarios = \App\Models\Funcionario::where('escola_id', $escolaId)
                          ->where('ativo', true)
                          ->orderBy('nome')
                          ->get();

        $itensDisponiveis = ItemBiblioteca::where('escola_id', $escolaId)
                            ->where('habilitado_emprestimo', true)
                            ->whereIn('status', ['disponivel', 'ativo'])
                            ->orderBy('id', 'asc')
                            ->get();

        // Retornar corpo do formulário para embutir em modal (fetch com Accept: text/html)
        $accept = $request->header('Accept', '');
        if (stripos($accept, 'text/html') !== false) {
            return view('biblioteca.emprestimos.partials.create_form', compact('item', 'alunos', 'funcionarios', 'itensDisponiveis'));
        }

        // Fallback para navegação tradicional: reutiliza dados do formulário
        return view('biblioteca.emprestimos.index', compact('alunos', 'funcionarios', 'itensDisponiveis'));
    }

    /**
     * Realizar empréstimo
     */
    public function store(Request $request)
    {

        $request->validate([
            'item_id' => 'required|exists:item_biblioteca,id',
            // usuario_id virá como "tipo:id" (ex.: funcionario:12 ou aluno:34)
            'usuario_id' => 'required|string',
            'observacoes' => 'nullable|string|max:500'
        ]);

        // Determinar escola considerando perfil do usuário
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
            return back()->withErrors(['item_id' => 'Item não encontrado na escola atual.']);
        }

        // Resolver usuário a partir do selecionado no modal (funcionário ou aluno)
        $selecionado = $request->usuario_id;
        [$tipoSelecionado, $idSelecionado] = array_pad(explode(':', $selecionado, 2), 2, null);

        if (!$tipoSelecionado || !$idSelecionado) {
            return back()->withErrors(['usuario_id' => 'Seleção de usuário inválida.']);
        }

        if ($tipoSelecionado === 'funcionario') {
            $func = \App\Models\Funcionario::where('escola_id', $escolaId)->findOrFail($idSelecionado);
            if (!$func->user_id) {
                return back()->withErrors(['usuario_id' => 'Funcionário selecionado não possui conta de usuário vinculada.']);
            }
            $usuario = User::where('id', $func->user_id)
                           ->where(function ($q) use ($escolaId) {
                               $q->where('escola_id', $escolaId)
                                 ->orWhereHas('escolas', function ($q2) use ($escolaId) {
                                     $q2->where('escola_id', $escolaId);
                                 });
                           })
                           ->firstOrFail();
        } elseif ($tipoSelecionado === 'aluno') {
            $aluno = \App\Models\Aluno::where('escola_id', $escolaId)->findOrFail($idSelecionado);
            // Estratégia robusta sem depender exclusivamente de e-mail:
            // 1) Tentar via user_id do aluno (se existir)
            // 2) Tentar via e-mail (se disponível) com cargo "Aluno"
            // 3) Tentar via nome completo com cargo "Aluno" (se houver correspondência única)

            $usuario = null;

            // 1) user_id direto
            if (property_exists($aluno, 'user_id') && !empty($aluno->user_id)) {
                $usuario = User::where('id', $aluno->user_id)
                               ->where(function ($q) use ($escolaId) {
                                   $q->where('escola_id', $escolaId)
                                     ->orWhereHas('escolas', function ($q2) use ($escolaId) {
                                         $q2->where('escola_id', $escolaId);
                                     });
                               })
                               ->whereHas('cargos', function ($q) {
                                   $q->where('nome', 'Aluno')->where('ativo', true);
                               })
                               ->first();
            }

            // 2) e-mail do aluno
            if (!$usuario && !empty($aluno->email)) {
                $usuario = User::where('email', $aluno->email)
                               ->where(function ($q) use ($escolaId) {
                                   $q->where('escola_id', $escolaId)
                                     ->orWhereHas('escolas', function ($q2) use ($escolaId) {
                                         $q2->where('escola_id', $escolaId);
                                     });
                               })
                               ->whereHas('cargos', function ($q) {
                                   $q->where('nome', 'Aluno')->where('ativo', true);
                               })
                               ->first();
            }

            // 3) nome completo único
            if (!$usuario) {
                $nomeCompleto = trim(($aluno->nome ?? '') . ' ' . ($aluno->sobrenome ?? ''));
                if (!empty($nomeCompleto)) {
                    $usuariosPorNome = User::where('name', $nomeCompleto)
                                            ->where(function ($q) use ($escolaId) {
                                                $q->where('escola_id', $escolaId)
                                                  ->orWhereHas('escolas', function ($q2) use ($escolaId) {
                                                      $q2->where('escola_id', $escolaId);
                                                  });
                                            })
                                            ->whereHas('cargos', function ($q) {
                                                $q->where('nome', 'Aluno')->where('ativo', true);
                                            })
                                            ->get();

                    if ($usuariosPorNome->count() === 1) {
                        $usuario = $usuariosPorNome->first();
                    } elseif ($usuariosPorNome->count() > 1) {
                        return back()->withErrors(['usuario_id' => 'Existe mais de um usuário Aluno com o nome do aluno nesta escola. Vincule explicitamente o aluno a um usuário (cargo Aluno).']);
                    }
                }
            }

            if (!$usuario) {
                return back()->withErrors(['usuario_id' => 'Aluno selecionado não possui usuário vinculado como Aluno. Crie/associe um usuário com cargo Aluno ou vincule o aluno a um usuário existente.']);
            }
        } else {
            return back()->withErrors(['usuario_id' => 'Tipo de usuário inválido.']);
        }

        // Verificar disponibilidade do item
        if (!$this->verificarDisponibilidade($item)) {
            return back()->withErrors(['item_id' => 'Item não disponível para empréstimo.']);
        }

        // Verificar políticas de acesso
        $politicaViolada = $this->verificarPoliticasAcesso($usuario, $item);
        if ($politicaViolada) {
            return back()->withErrors(['usuario_id' => $politicaViolada]);
        }

        DB::beginTransaction();
        try {
            // Cancelar reserva se existir
            $this->cancelarReservaSeExistir($item->id, $usuario->id);

            // Calcular data prevista de devolução
            $prazo = $this->obterPrazoEmprestimo($usuario, $item);
            $dataPrevista = Carbon::now()->addDays($prazo);

            // Criar empréstimo
            $emprestimo = Emprestimo::create([
                'escola_id' => $escolaId,
                'item_id' => $item->id,
                'usuario_id' => $usuario->id,
                'data_emprestimo' => now(),
                'data_prevista' => $dataPrevista,
                'status' => 'ativo',
                'observacoes' => $request->observacoes
            ]);
            Historico::registrar('criado', 'Emprestimo', $emprestimo->id, null, $emprestimo->toArray());

            DB::commit();

            // Responder em JSON para submissões via AJAX no modal
            if ($request->expectsJson()) {
                // Atualizar listagem de reservas para retorno padronizado
                $reservas = \App\Models\Reserva::with(['item', 'usuario'])
                    ->where('escola_id', $escolaId)
                    ->orderBy('created_at', 'desc')
                    ->paginate(20);
                $reservas->setCollection(
                    $reservas->getCollection()->map(function ($r) {
                        // Se houver método calcularPosicaoFila em ReservaController, não disponível aqui
                        // então mantemos sem posição ou recalculamos no frontend
                        return $r;
                    })
                );
                $htmlList = view('biblioteca.reservas._list', compact('reservas'))->render();

                return response()->json([
                    'success' => true,
                    'message' => 'Empréstimo realizado com sucesso!',
                    'emprestimo_id' => $emprestimo->id,
                    'html_list' => $htmlList,
                ]);
            }

            return redirect()->route('biblioteca.emprestimos.index')
                           ->with('success', 'Empréstimo realizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollback();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao realizar empréstimo: ' . $e->getMessage(),
                ], 422);
            }
            return back()->withErrors(['error' => 'Erro ao realizar empréstimo: ' . $e->getMessage()]);
        }
    }

    /**
     * Mostrar detalhes do empréstimo
     */
    public function show(Request $request, Emprestimo $emprestimo)
    {
        $this->authorize('view', $emprestimo);
        
        $emprestimo->load(['item', 'usuario']);
        
        // Se solicitado como parcial (para modal embutido na listagem), renderiza apenas o conteúdo
        if ($request->boolean('partial')) {
            return view('biblioteca.emprestimos.partials.detalhes', compact('emprestimo'));
        }
        // Caso não seja parcial, redireciona para a listagem com parâmetro para abrir modal
        return redirect()->route('biblioteca.emprestimos.index', ['detalhes' => $emprestimo->id]);
    }

    /**
     * Processar devolução
     */
    public function devolver(Request $request, Emprestimo $emprestimo)
    {
        $this->authorize('update', $emprestimo);

        $request->validate([
            'observacoes_devolucao' => 'nullable|string|max:500'
        ]);

        if ($emprestimo->status !== 'ativo') {
            return back()->withErrors(['error' => 'Este empréstimo não está ativo.']);
        }

        DB::beginTransaction();
        try {
            // Calcular multa se houver atraso
            $multa = $this->calcularMulta($emprestimo);

            $dadosAntigos = $emprestimo->toArray();
            $emprestimo->update([
                'data_devolucao' => now(),
                'status' => 'devolvido',
                'multa_calculada' => $multa,
                'observacoes_devolucao' => $request->observacoes_devolucao
            ]);
            $dadosNovos = $emprestimo->fresh()->toArray();
            Historico::registrar('atualizado', 'Emprestimo', $emprestimo->id, $dadosAntigos, $dadosNovos);

            DB::commit();

            $message = 'Devolução processada com sucesso!';
            if ($multa > 0) {
                $message .= ' Multa aplicada: R$ ' . number_format($multa, 2, ',', '.');
            }

            return redirect()->route('biblioteca.emprestimos.index')
                           ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Erro ao processar devolução: ' . $e->getMessage()]);
        }
    }

    /**
     * Renovar empréstimo
     */
    public function renovar(Emprestimo $emprestimo)
    {
        $this->authorize('update', $emprestimo);

        if ($emprestimo->status !== 'ativo') {
            return back()->withErrors(['error' => 'Este empréstimo não está ativo.']);
        }

        if ($emprestimo->data_prevista < now()) {
            return back()->withErrors(['error' => 'Não é possível renovar empréstimo em atraso.']);
        }

        // Verificar se há reservas pendentes para o item
        if ($emprestimo->item->reservas()->where('status', 'ativa')->exists()) {
            return back()->withErrors(['error' => 'Item possui reservas pendentes. Não é possível renovar.']);
        }

        $prazo = $this->obterPrazoEmprestimo($emprestimo->usuario, $emprestimo->item);
        $novaDataPrevista = Carbon::parse($emprestimo->data_prevista)->addDays($prazo);

        $dadosAntigos = $emprestimo->toArray();
        $emprestimo->update([
            'data_prevista' => $novaDataPrevista
        ]);
        $dadosNovos = $emprestimo->fresh()->toArray();
        Historico::registrar('atualizado', 'Emprestimo', $emprestimo->id, $dadosAntigos, $dadosNovos);

        return redirect()->route('biblioteca.emprestimos.index')
            ->with('success', 'Empréstimo renovado até ' . $novaDataPrevista->format('d/m/Y'));
    }

    /**
     * Verificar disponibilidade do item
     */
    private function verificarDisponibilidade(ItemBiblioteca $item): bool
    {
        // Tratar itens com status 'disponivel' ou 'ativo' como elegíveis.
        if (!$item->habilitado_emprestimo || !in_array($item->status, ['disponivel', 'ativo'])) {
            return false;
        }

        $emprestimosAtivos = $item->emprestimos()->where('status', 'ativo')->count();

        return $emprestimosAtivos < (int) $item->quantidade_fisica;
    }

    /**
     * Verificar políticas de acesso
     */
    private function verificarPoliticasAcesso(User $usuario, ItemBiblioteca $item): ?string
    {
        $perfil = $this->obterPerfilUsuario($usuario);
        $escolaId = $item->escola_id; // Políticas são por escola do item

        // Política global da biblioteca por escola
        $politicaGlobal = BibliotecaPolitica::where('escola_id', $escolaId)->first();

        if ($politicaGlobal) {
            // Elegibilidade por perfil
            if ($perfil === 'aluno' && !$politicaGlobal->permitir_alunos) {
                return 'Alunos não estão habilitados a realizar empréstimos.';
            }
            if ($perfil === 'funcionario' && !$politicaGlobal->permitir_funcionarios) {
                return 'Funcionários não estão habilitados a realizar empréstimos.';
            }

            // Bloqueio por multas/atrasos pendentes
            if ($politicaGlobal->bloquear_por_multas) {
                $atrasosPendentes = Emprestimo::where('escola_id', $escolaId)
                    ->where('usuario_id', $usuario->id)
                    ->where('status', 'ativo')
                    ->where('data_prevista', '<', now())
                    ->count();
                if ($atrasosPendentes > 0) {
                    return 'Empréstimo bloqueado: existem empréstimos em atraso pendentes.';
                }
            }
        }

        // Política por perfil/tipo de item
        $politicaPerfilTipo = PoliticaAcesso::where('escola_id', $escolaId)
                                 ->where('perfil', $perfil)
                                 ->where('tipo_item', $item->tipo)
                                 ->first();

        // Verificar limite de empréstimos ativos (global e por perfil/tipo)
        $emprestimosAtivos = Emprestimo::where('escola_id', $escolaId)
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
     * Obter prazo de empréstimo baseado na política
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

        $global = BibliotecaPolitica::where('escola_id', $escolaId)->first();
        return $global && $global->prazo_padrao_dias ? (int) $global->prazo_padrao_dias : 7;
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
     * Cancelar reserva se existir
     */
    private function cancelarReservaSeExistir(int $itemId, int $usuarioId): void
    {
        $reserva = \App\Models\Reserva::where('item_id', $itemId)
                                     ->where('usuario_id', $usuarioId)
                                     ->where('status', 'ativa')
                                     ->first();

        if ($reserva) {
            $reserva->update(['status' => 'processada']);
        }
    }

    /**
     * Calcular multa por atraso
     */
    private function calcularMulta(Emprestimo $emprestimo): float
    {
        if ($emprestimo->data_prevista >= now()) {
            return 0.0;
        }

        $diasAtraso = Carbon::parse($emprestimo->data_prevista)->diffInDays(now());
        
        $multaRegra = \App\Models\MultaRegra::where('escola_id', $emprestimo->escola_id)->first();
        
        if (!$multaRegra) {
            return 0.0;
        }

        $multa = $diasAtraso * $multaRegra->taxa_por_dia;
        
        return min($multa, $multaRegra->valor_maximo);
    }
}
