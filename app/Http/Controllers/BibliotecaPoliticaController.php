<?php

namespace App\Http\Controllers;

use App\Models\BibliotecaPolitica;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BibliotecaPoliticaController extends Controller
{
    protected function obterEscolaId(): int
    {
        if (Auth::check()) {
            $userEscolaId = Auth::user()->escola_id ?? null;
            if ($userEscolaId) {
                return (int) $userEscolaId;
            }
        }
        // Fallback para sessão quando usuário não tiver escola primária definida
        $sessionEscola = Session::get('escola_atual') ?? Session::get('escola_id');
        return (int) ($sessionEscola ?: 1);
    }

    public function show(Request $request)
    {
        $escolaId = $this->obterEscolaId();
        $politica = BibliotecaPolitica::firstOrCreate(
            ['escola_id' => $escolaId],
            [
                'permitir_funcionarios' => true,
                'permitir_alunos' => true,
                'max_emprestimos_por_usuario' => 3,
                'prazo_padrao_dias' => 7,
                'bloquear_por_multas' => false,
            ]
        );

        return response()->json($politica);
    }

    public function update(Request $request)
    {
        $escolaId = $this->obterEscolaId();
        $data = $request->validate([
            'permitir_funcionarios' => 'required|boolean',
            'permitir_alunos' => 'required|boolean',
            'max_emprestimos_por_usuario' => 'nullable|integer|min:1|max:100',
            'prazo_padrao_dias' => 'nullable|integer|min:1|max:60',
            'bloquear_por_multas' => 'required|boolean',
        ]);

        $politica = BibliotecaPolitica::firstOrCreate(['escola_id' => $escolaId]);
        $politica->fill($data);
        $politica->save();

        return response()->json($politica);
    }
}