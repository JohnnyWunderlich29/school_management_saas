<?php

namespace App\Http\Controllers;

use App\Models\Transferencia;
use App\Models\Aluno;
use App\Models\Turma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class TransferenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Determinar escola atual seguindo padrão dos demais controllers
        $user = auth()->user();
        $isSuperAdminOrSupport = $user->isSuperAdmin() || $user->temCargo('Suporte');
        $escolaId = $isSuperAdminOrSupport ? (session('escola_atual') ?: $user->escola_id) : $user->escola_id;

        $query = Transferencia::with(['aluno', 'turmaOrigem', 'turmaDestino', 'solicitante', 'aprovador'])
            ->orderBy('created_at', 'desc');

        // Manter contexto da escola: filtra por escola do aluno
        if ($escolaId) {
            $query->whereHas('aluno', function($q) use ($escolaId) {
                $q->where('escola_id', $escolaId);
            });
        }

        // Filtro por status (pendente/aprovada/rejeitada) se informado
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        // Filtro por nome do aluno se informado
        if ($request->filled('aluno')) {
            $alunoTerm = $request->string('aluno')->toString();
            $query->whereHas('aluno', function($q) use ($alunoTerm) {
                $q->where(function($qq) use ($alunoTerm) {
                    $qq->where('nome', 'like', "%$alunoTerm%")
                       ->orWhere('sobrenome', 'like', "%$alunoTerm%");
                });
            });
        }

        $transferencias = $query->paginate(15);

        return view('transferencias.index', compact('transferencias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $alunos = Aluno::with('turma')->where('ativo', true)->get();
        $turmas = Turma::where('ativo', true)->withCount('alunos')->get();
        
        return view('transferencias.create', compact('alunos', 'turmas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'aluno_id' => 'required|exists:alunos,id',
            'turma_destino_id' => 'required|exists:turmas,id',
            'motivo' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $aluno = Aluno::findOrFail($request->aluno_id);
            $turmaDestino = Turma::findOrFail($request->turma_destino_id);

            // Verificar se o aluno já está na sala de destino
            if ($aluno->turma_id == $turmaDestino->id) {
                return redirect()->back()
                    ->with('warning', 'O aluno já está matriculado nesta turma.');
            }

            // Verificar se já existe uma transferência pendente para este aluno
            $transferenciaPendente = Transferencia::where('aluno_id', $request->aluno_id)
                ->where('status', 'pendente')
                ->first();

            if ($transferenciaPendente) {
                return redirect()->back()
                    ->with('warning', 'Já existe uma transferência pendente para este aluno.');
            }

            // Não checar capacidade aqui; capacidade será validada na aprovação

            Transferencia::create([
                'aluno_id' => $request->aluno_id,
                'turma_id' => $aluno->turma_id,
                'turma_destino_id' => $request->turma_destino_id,
                'solicitante_id' => Auth::id(),
                'motivo' => $request->motivo,
                'status' => 'pendente',
                'data_solicitacao' => Carbon::now()
            ]);

            return redirect()->route('transferencias.index')
                ->with('success', 'Solicitação de transferência criada com sucesso!');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erro ao criar solicitação: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transferencia $transferencia)
    {
        $transferencia->load(['aluno', 'turmaOrigem', 'turmaDestino', 'solicitante', 'aprovador']);
        return view('transferencias.show', compact('transferencia'));
    }

    /**
     * Show the form for approving a transfer.
     */
    public function showAprovar(Transferencia $transferencia)
    {
        $transferencia->load(['aluno', 'turmaOrigem', 'turmaDestino', 'solicitante']);
        return view('transferencias.aprovar', compact('transferencia'));
    }

    /**
     * Show the form for rejecting a transfer.
     */
    public function showRejeitar(Transferencia $transferencia)
    {
        $transferencia->load(['aluno', 'turmaOrigem', 'turmaDestino', 'solicitante']);
        return view('transferencias.rejeitar', compact('transferencia'));
    }

    /**
     * Aprovar transferência
     */
    public function aprovar(Request $request, Transferencia $transferencia)
    {
        // Restringir aprovação a coordenadores (ou admin, se aplicável)
        $user = Auth::user();
        if (!$user->isAdminOrCoordinator() && !$user->hasRole('coordenador') && !$user->temCargo('Coordenador')) {
            return redirect()->back()->with('error', 'Apenas coordenadores podem aprovar transferências.');
        }

        // Verificação de coordenação por turma (se aplicável) pode ser adicionada aqui

        $validator = Validator::make($request->all(), [
            'observacoes_aprovador' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        if ($transferencia->status !== 'pendente') {
            return redirect()->back()
                ->with('error', 'Esta transferência não pode ser aprovada.');
        }

        try {
            DB::beginTransaction();

            // Verificar capacidade da turma de destino novamente
            $turmaDestino = $transferencia->turmaDestino;
            if ($turmaDestino->alunos()->count() >= $turmaDestino->capacidade) {
                return redirect()->back()
                    ->with('error', 'A turma de destino já atingiu sua capacidade máxima.');
            }

            // Atualizar a transferência
            $transferencia->update([
                'status' => 'aprovada',
                'aprovador_id' => Auth::id(),
                'observacoes_aprovador' => $request->observacoes_aprovador,
                'data_aprovacao' => Carbon::now()
            ]);

            // Transferir o aluno: atualizar turma
            $transferencia->aluno->update([
                'turma_id' => $transferencia->turma_destino_id,
            ]);

            DB::commit();

            return redirect()->route('transferencias.index')
                ->with('success', 'Transferência aprovada e aluno transferido com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Erro ao aprovar transferência: ' . $e->getMessage());
        }
    }

    /**
     * Rejeitar transferência
     */
    public function rejeitar(Request $request, Transferencia $transferencia)
    {
        $validator = Validator::make($request->all(), [
            'observacoes_aprovador' => 'required|string|max:500'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        if ($transferencia->status !== 'pendente') {
            return redirect()->back()
                ->with('error', 'Esta transferência não pode ser rejeitada.');
        }

        $transferencia->update([
            'status' => 'rejeitada',
            'aprovador_id' => Auth::id(),
            'observacoes_aprovador' => $request->observacoes_aprovador,
            'data_aprovacao' => Carbon::now()
        ]);

        return redirect()->route('transferencias.index')
            ->with('success', 'Transferência rejeitada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transferencia $transferencia)
    {
        if ($transferencia->status === 'aprovada') {
            return redirect()->back()
                ->with('error', 'Não é possível excluir uma transferência já aprovada.');
        }

        $transferencia->delete();

        return redirect()->route('transferencias.index')
            ->with('success', 'Transferência excluída com sucesso!');
    }
}
