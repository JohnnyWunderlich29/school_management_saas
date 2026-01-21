<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\Disciplina;
use App\Models\Nota;
use App\Models\Turma;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotaController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'aluno_id' => 'required|exists:alunos,id',
            'disciplina_id' => 'required|exists:disciplinas,id',
            'valor' => 'required|numeric|min:0|max:10',
            'referencia' => 'required|string|max:255',
            'data_lancamento' => 'required|date',
            'observacoes' => 'nullable|string'
        ]);

        $aluno = Aluno::findOrFail($validated['aluno_id']);
        
        $user = Auth::user();
        $isSuperAdmin = $user->temCargo('Super Administrador');
        $isSupport = $user->temCargo('Suporte');

        // Garantir que o aluno pertence à escola atual (segurança extra além do middleware)
        if (!$isSuperAdmin && !$isSupport) {
            $escolaAtual = session('escola_atual');
            if ($aluno->escola_id !== $escolaAtual) {
                abort(403);
            }
        }

        $nota = Nota::create([
            'aluno_id' => $validated['aluno_id'],
            'disciplina_id' => $validated['disciplina_id'],
            'professor_id' => Auth::user()->funcionario?->id,
            'escola_id' => $aluno->escola_id, // Usar escola do aluno
            'valor' => $validated['valor'],
            'referencia' => $validated['referencia'],
            'data_lancamento' => $validated['data_lancamento'],
            'observacoes' => $validated['observacoes']
        ]);

        return redirect()->back()->with('success', 'Nota lançada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nota $nota)
    {
        $user = Auth::user();
        if (!$user->temCargo('Super Administrador') && !$user->temCargo('Suporte')) {
            if ($nota->escola_id !== session('escola_atual')) {
                abort(403);
            }
        }

        $nota->delete();

        return redirect()->back()->with('success', 'Nota excluída com sucesso!');
    }

    /**
     * API para buscar disciplinas da turma do aluno
     */
    public function getDisciplinasByAluno(Aluno $aluno)
    {
        $user = Auth::user();
        if (!$user->temCargo('Super Administrador') && !$user->temCargo('Suporte')) {
            if ($aluno->escola_id !== session('escola_atual')) {
                return response()->json(['error' => 'Acesso negado.'], 403);
            }
        }

        $disciplinas = [];
        
        if ($aluno->turma) {
            // Buscar disciplinas vinculadas à turma do aluno via grade_aulas
            $disciplinas = $aluno->turma->gradeAulas()
                ->with('disciplina')
                ->get()
                ->pluck('disciplina')
                ->unique('id')
                ->values();
        }

        return response()->json($disciplinas);
    }
}
