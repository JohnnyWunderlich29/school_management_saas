<?php

namespace App\Http\Controllers;

use App\Models\Despesa;
use App\Models\RecorrenciaDespesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DespesaController extends Controller
{
    /**
     * Lista despesas com filtros e paginação.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $escolaId = ($user->isSuperAdmin() || $user->temCargo('Suporte'))
            ? (Session::get('escola_atual') ?: $user->escola_id)
            : $user->escola_id;

        $query = Despesa::query();

        if ($escolaId) {
            $query->where('escola_id', $escolaId);
        }

        // Filtro de status: padrão 'pendente' sempre que não informado
        $status = $request->input('status');
        if (empty($status)) {
            $status = 'pendente';
        }
        $query->where('status', $status);
        if ($request->filled('categoria')) {
            $query->where('categoria', 'like', '%' . $request->input('categoria') . '%');
        }
        if ($request->filled('descricao')) {
            $query->where('descricao', 'like', '%' . $request->input('descricao') . '%');
        }
        if ($request->filled('de')) {
            $query->whereDate('data', '>=', $request->input('de'));
        }
        if ($request->filled('ate')) {
            $query->whereDate('data', '<=', $request->input('ate'));
        }

        // Ordenação
        $allowedSorts = ['data', 'descricao', 'categoria', 'valor', 'status'];
        $sort = $request->input('sort');
        $direction = strtolower($request->input('direction', 'desc'));
        $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'asc';

        if ($sort && in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction)
                ->orderBy('id', 'desc'); // desempatador estável
        } else {
            $query->orderBy('data', 'desc')
                ->orderBy('id', 'desc');
        }

        $despesas = $query->paginate(15)->withQueryString();

        $statusOptions = [
            'pendente' => 'Pendente',
            'liquidada' => 'Liquidada',
            'cancelada' => 'Cancelada',
        ];

        return view('admin.despesas.index', [
            'despesas' => $despesas,
            'statusOptions' => $statusOptions,
            'filtros' => [
                'status' => $request->input('status'),
                'categoria' => $request->input('categoria'),
                'descricao' => $request->input('descricao'),
                'de' => $request->input('de'),
                'ate' => $request->input('ate'),
            ],
        ]);
    }

    /**
     * Cria uma nova despesa.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'data' => ['required', 'date'],
            'valor' => ['required', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:pendente,liquidada'],
        ]);

        $user = Auth::user();
        $escolaId = ($user->isSuperAdmin() || $user->temCargo('Suporte'))
            ? (Session::get('escola_atual') ?: $user->escola_id)
            : $user->escola_id;

        if (!$escolaId) {
            return redirect()->back()->with('error', 'Selecione uma escola antes de criar despesas.');
        }

        $data['escola_id'] = $escolaId;
        $data['status'] = $data['status'] ?? 'pendente';

        try {
            Despesa::create($data);
            return redirect()->route('admin.despesas.index')->with('success', 'Despesa criada com sucesso.');
        } catch (\Exception $e) {
            Log::error('Erro ao criar despesa', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erro ao criar despesa.')->withInput();
        }
    }

    /**
     * Atualiza uma despesa existente.
     */
    public function update(Request $request, Despesa $despesa)
    {
        $data = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'data' => ['required', 'date'],
            'valor' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'in:pendente,liquidada,cancelada'],
        ]);

        $user = Auth::user();
        $escolaId = ($user->isSuperAdmin() || $user->temCargo('Suporte'))
            ? (Session::get('escola_atual') ?: $user->escola_id)
            : $user->escola_id;

        if ($despesa->escola_id && $escolaId && $despesa->escola_id !== $escolaId) {
            return response()->json(['success' => false, 'message' => 'Despesa não pertence à escola selecionada.'], 403);
        }

        try {
            $despesa->update($data);
            return response()->json(['success' => true, 'message' => 'Despesa atualizada com sucesso.']);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar despesa', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar despesa.'], 500);
        }
    }

    /**
     * Cancela uma despesa com motivo.
     */
    public function cancel(Request $request, Despesa $despesa)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:3'],
        ]);

        $user = Auth::user();
        $escolaId = ($user->isSuperAdmin() || $user->temCargo('Suporte'))
            ? (Session::get('escola_atual') ?: $user->escola_id)
            : $user->escola_id;

        if ($despesa->escola_id && $escolaId && $despesa->escola_id !== $escolaId) {
            return response()->json(['success' => false, 'message' => 'Despesa não pertence à escola selecionada.'], 403);
        }

        try {
            $despesa->update([
                'status' => 'cancelada',
                'cancelamento_motivo' => $data['reason'],
                'cancelado_por' => $user->id,
                'cancelado_em' => Carbon::now(),
            ]);

            return response()->json(['success' => true, 'message' => 'Despesa cancelada com sucesso.']);
        } catch (\Exception $e) {
            Log::error('Erro ao cancelar despesa', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erro ao cancelar despesa.'], 500);
        }
    }

    /**
     * Dados para preencher modal de edição via AJAX.
     */
    public function editModal(Despesa $despesa)
    {
        try {
            $data = [
                'id' => $despesa->id,
                'descricao' => $despesa->descricao,
                'categoria' => $despesa->categoria,
                'data' => optional($despesa->data)->format('Y-m-d'),
                'valor' => number_format((float) $despesa->valor, 2, '.', ''),
                'status' => $despesa->status,
                'status_options' => [
                    ['value' => 'pendente', 'label' => 'Pendente'],
                    ['value' => 'liquidada', 'label' => 'Liquidada'],
                    ['value' => 'cancelada', 'label' => 'Cancelada'],
                ],
                'update_url' => route('admin.despesas.update', $despesa),
            ];

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar modal de despesa', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erro ao carregar dados da despesa.'], 500);
        }
    }

    /**
     * Lista modelos de despesas recorrentes.
     */
    public function recorrenciasIndex(Request $request)
    {
        $user = Auth::user();
        $escolaId = ($user->isSuperAdmin() || $user->temCargo('Suporte'))
            ? (Session::get('escola_atual') ?: $user->escola_id)
            : $user->escola_id;

        $recorrencias = RecorrenciaDespesa::where('escola_id', $escolaId)
            ->orderBy('descricao')
            ->get();

        return view('admin.despesas.recorrencias', [
            'recorrencias' => $recorrencias
        ]);
    }

    /**
     * Salva um novo template de recorrência.
     */
    public function recorrenciasStore(Request $request)
    {
        $data = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'valor' => ['required', 'numeric', 'min:0'],
            'frequencia' => ['required', 'in:semanal,mensal,anual'],
            'dia_vencimento' => ['nullable', 'integer', 'min:1', 'max:31'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
        ]);

        $user = Auth::user();
        $escolaId = ($user->isSuperAdmin() || $user->temCargo('Suporte'))
            ? (Session::get('escola_atual') ?: $user->escola_id)
            : $user->escola_id;

        if (!$escolaId) {
            return redirect()->back()->with('error', 'Selecione uma escola.');
        }

        $data['escola_id'] = $escolaId;
        $data['proxima_geracao'] = $data['data_inicio'];
        $data['ativo'] = true;

        try {
            DB::beginTransaction();
            $recorrencia = RecorrenciaDespesa::create($data);

            // Gerar a primeira se a data de início for hoje ou no passado
            if (Carbon::parse($data['data_inicio'])->lte(Carbon::today())) {
                Despesa::create([
                    'escola_id' => $escolaId,
                    'recorrencia_id' => $recorrencia->id,
                    'descricao' => $data['descricao'],
                    'categoria' => $data['categoria'],
                    'valor' => $data['valor'],
                    'data' => $data['data_inicio'],
                    'status' => 'pendente',
                ]);

                // Atualizar proxima_geracao
                $frequencia = $data['frequencia'];
                $atual = Carbon::parse($data['data_inicio']);
                $proxima = match ($frequencia) {
                    'semanal' => $atual->addWeek(),
                    'mensal' => $atual->addMonthNoOverflow()->startOfMonth(),
                    'anual' => $atual->addYear(),
                    default => $atual->addMonthNoOverflow()->startOfMonth(),
                };

                if ($recorrencia->data_fim && $proxima->gt($recorrencia->data_fim)) {
                    $recorrencia->ativo = false;
                }
                $recorrencia->proxima_geracao = $proxima;
                $recorrencia->save();
            }

            DB::commit();
            return redirect()->route('admin.despesas.recorrencias')->with('success', 'Recorrência configurada com sucesso.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao salvar recorrência', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Erro ao salvar recorrência.');
        }
    }

    /**
     * Alterna status da recorrência (Ativo/Inativo).
     */
    public function recorrenciasToggle(RecorrenciaDespesa $recorrencia)
    {
        $user = Auth::user();
        $escolaId = ($user->isSuperAdmin() || $user->temCargo('Suporte'))
            ? (Session::get('escola_atual') ?: $user->escola_id)
            : $user->escola_id;

        if ($recorrencia->escola_id !== $escolaId) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        try {
            $recorrencia->update(['ativo' => !$recorrencia->ativo]);
            return response()->json(['success' => true, 'message' => 'Status atualizado.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar status.'], 500);
        }
    }

    /**
     * Remove uma recorrência.
     */
    public function recorrenciasDestroy(RecorrenciaDespesa $recorrencia)
    {
        $user = Auth::user();
        $escolaId = ($user->isSuperAdmin() || $user->temCargo('Suporte'))
            ? (Session::get('escola_atual') ?: $user->escola_id)
            : $user->escola_id;

        if ($recorrencia->escola_id !== $escolaId) {
            return redirect()->back()->with('error', 'Acesso negado.');
        }

        try {
            $recorrencia->delete();
            return redirect()->route('admin.despesas.recorrencias')->with('success', 'Recorrência removida com sucesso.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao remover recorrência.');
        }
    }

    /**
     * Retorna dados para editar uma recorrência via modal.
     */
    public function recorrenciaEdit(RecorrenciaDespesa $recorrencia)
    {
        $user = Auth::user();
        $escolaId = ($user->isSuperAdmin() || $user->temCargo('Suporte'))
            ? (Session::get('escola_atual') ?: $user->escola_id)
            : $user->escola_id;

        if ($recorrencia->escola_id !== $escolaId) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $recorrencia->id,
                'descricao' => $recorrencia->descricao,
                'categoria' => $recorrencia->categoria,
                'valor' => $recorrencia->valor,
                'frequencia' => $recorrencia->frequencia,
                'data_inicio' => $recorrencia->data_inicio->format('Y-m-d'),
                'data_fim' => $recorrencia->data_fim ? $recorrencia->data_fim->format('Y-m-d') : null,
                'update_url' => route('admin.despesas.recorrencias.update', $recorrencia),
            ]
        ]);
    }

    /**
     * Atualiza um template de recorrência.
     */
    public function recorrenciaUpdate(Request $request, RecorrenciaDespesa $recorrencia)
    {
        $data = $request->validate([
            'descricao' => ['required', 'string', 'max:255'],
            'categoria' => ['nullable', 'string', 'max:100'],
            'valor' => ['required', 'numeric', 'min:0'],
            'frequencia' => ['required', 'in:semanal,mensal,anual'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
        ]);

        $user = Auth::user();
        $escolaId = ($user->isSuperAdmin() || $user->temCargo('Suporte'))
            ? (Session::get('escola_atual') ?: $user->escola_id)
            : $user->escola_id;

        if ($recorrencia->escola_id !== $escolaId) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        try {
            $recorrencia->update($data);
            return response()->json(['success' => true, 'message' => 'Recorrência atualizada com sucesso.']);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar recorrência', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar recorrência.'], 500);
        }
    }

    /**
     * Marca uma despesa como liquidada.
     */
    public function liquidar(Despesa $despesa)
    {
        $user = Auth::user();
        $escolaId = ($user->isSuperAdmin() || $user->temCargo('Suporte'))
            ? (Session::get('escola_atual') ?: $user->escola_id)
            : $user->escola_id;

        if ($despesa->escola_id !== $escolaId) {
            return response()->json(['success' => false, 'message' => 'Acesso negado.'], 403);
        }

        if ($despesa->status !== 'pendente') {
            return response()->json(['success' => false, 'message' => 'Somente despesas pendentes podem ser liquidadas.'], 422);
        }

        try {
            $despesa->update(['status' => 'liquidada']);
            return response()->json(['success' => true, 'message' => 'Despesa liquidada com sucesso!']);
        } catch (\Exception $e) {
            Log::error('Erro ao liquidar despesa', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Erro ao liquidar despesa.'], 500);
        }
    }
}