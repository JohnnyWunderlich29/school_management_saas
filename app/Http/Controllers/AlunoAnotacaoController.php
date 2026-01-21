<?php

namespace App\Http\Controllers;

use App\Models\Aluno;
use App\Models\AlunoAnotacao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlunoAnotacaoController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'aluno_id' => 'required|exists:alunos,id',
            'tipo' => 'required|in:comum,grave,elogio,advertencia',
            'titulo' => 'required|string|max:255',
            'descricao' => 'required|string',
            'data_ocorrencia' => 'required|date',
        ]);

        $aluno = Aluno::findOrFail($validated['aluno_id']);

        // Obter usuário autenticado
        $user = Auth::user();
        $isSuperAdmin = $user->temCargo('Super Administrador');
        $isSupport = $user->temCargo('Suporte');

        // Garantir que o aluno pertence à escola atual
        // Super admins e suporte podem ignorar a restrição de contexto de sessão se estiverem fora de uma escola
        if (! $isSuperAdmin && ! $isSupport) {
            $escolaAtual = session('escola_atual');
            if ($aluno->escola_id !== $escolaAtual) {
                abort(403);
            }
        }

        AlunoAnotacao::create([
            'aluno_id' => $validated['aluno_id'],
            'escola_id' => $aluno->escola_id, // Usar id da escola do aluno para garantir consistência
            'usuario_id' => Auth::id(),
            'tipo' => $validated['tipo'],
            'titulo' => $validated['titulo'],
            'descricao' => $validated['descricao'],
            'data_ocorrencia' => $validated['data_ocorrencia'],
        ]);

        return redirect()->back()->with('success', 'Anotação registrada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AlunoAnotacao $anotacao)
    {
        $user = Auth::user();
        if (! $user->temCargo('Super Administrador') && ! $user->temCargo('Suporte')) {
            if ($anotacao->escola_id !== session('escola_atual')) {
                abort(403);
            }
        }

        $anotacao->delete();

        return redirect()->back()->with('success', 'Anotação removida com sucesso!');
    }
}
