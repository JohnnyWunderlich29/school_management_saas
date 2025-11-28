<?php

namespace App\Http\Controllers;

use App\Models\Turno;
use App\Models\Historico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TurnoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Turno::ordenados();
        
        // Para super admins e suporte, filtrar pela escola da sessão se definida
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $query->where('escola_id', session('escola_atual'));
            }
        } else {
            // Para usuários normais, filtrar por sua escola
            if (auth()->user()->escola_id) {
                $query->where('escola_id', auth()->user()->escola_id);
            }
        }

        // Filtros
        if ($request->filled('ativo')) {
            if ($request->ativo == '1') {
                $query->ativas();
            } else {
                $query->where('ativo', false);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('codigo', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        $turnos = $query->paginate(15);

        return view('admin.turnos.index', compact('turnos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Verificar se usuário tem escola associada
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->temCargo('Suporte') && !auth()->user()->escola_id) {
            return redirect()->route('admin.turnos.index')
                ->with('error', 'Você precisa estar associado a uma escola para criar turnos.');
        }
        
        return view('admin.turnos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
        
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('turnos')->where(function ($query) use ($escolaId) {
                    return $query->where('escola_id', $escolaId);
                })
            ],
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fim' => 'required|date_format:H:i|after:hora_inicio',
            'descricao' => 'nullable|string|max:500',
            'ordem' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['ativo'] = $request->has('ativo');
        
        // Definir escola_id baseado no usuário
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            $data['escola_id'] = session('escola_atual') ?: auth()->user()->escola_id;
        } else {
            $data['escola_id'] = auth()->user()->escola_id;
        }
        
        // Verificar se escola_id foi definido
        if (!$data['escola_id']) {
            return redirect()->back()
                ->with('error', 'Não foi possível determinar a escola. Selecione uma escola primeiro.')
                ->withInput();
        }

        $turno = Turno::create($data);
        Historico::registrar('criado', 'Turno', $turno->id, null, $turno->toArray());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Turno criado com sucesso!',
                'turno' => $turno
            ]);
        }

        return redirect()->route('admin.configuracoes.index', ['tab' => 'turnos'])
            ->with('success', 'Turno criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Turno $turno)
    {
        // Verificar se o usuário pode acessar este turno
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $turno->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turno->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }
        
        $turno->load('salas');
        return view('admin.turnos.show', compact('turno'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Turno $turno)
    {
        // Verificar se o usuário pode editar este turno
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $turno->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turno->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }
        
        return view('admin.turnos.edit', compact('turno'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Turno $turno)
    {
        // Verificar se o usuário pode atualizar este turno
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $turno->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turno->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }
        
        $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
        
        $validator = Validator::make($request->all(), [
            'nome' => 'required|string|max:255',
            'codigo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('turnos')->where(function ($query) use ($escolaId) {
                    return $query->where('escola_id', $escolaId);
                })->ignore($turno->id)
            ],
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fim' => 'required|date_format:H:i|after:hora_inicio',
            'descricao' => 'nullable|string|max:500',
            'ordem' => 'nullable|integer|min:1'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $data = $request->all();
        $data['ativo'] = $request->has('ativo');

        $dadosAntigos = $turno->toArray();
        $turno->update($data);
        $dadosNovos = $turno->fresh()->toArray();
        Historico::registrar('atualizado', 'Turno', $turno->id, $dadosAntigos, $dadosNovos);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Turno atualizado com sucesso!',
                'turno' => $turno
            ]);
        }

        return redirect()->route('admin.configuracoes.index', ['tab' => 'turnos'])
            ->with('success', 'Turno atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Turno $turno)
    {
        // Verificar se o usuário pode excluir este turno
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $turno->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turno->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }
        
        // Verificar se o turno tem salas associadas
        if ($turno->salas()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Não é possível excluir este turno pois existem salas associadas a ele.');
        }

        $dadosAntigos = $turno->toArray();
        $turno->delete();
        Historico::registrar('excluido', 'Turno', $turno->id, $dadosAntigos, null);

        return redirect()->route('admin.turnos.index')
            ->with('success', 'Turno excluído com sucesso!');
    }

    /**
     * Listar turnos para uso em modais/AJAX
     */
    public function listar(Request $request)
    {
        $query = Turno::ordenados();
        
        // Para super admins e suporte, filtrar pela escola da sessão se definida
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $query->where('escola_id', session('escola_atual'));
            }
        } else {
            // Para usuários normais, filtrar por sua escola
            if (auth()->user()->escola_id) {
                $query->where('escola_id', auth()->user()->escola_id);
            }
        }

        if (!$request->boolean('all')) {
            $query->where('ativo', true);
        }

        $turnos = $query->get(['id', 'nome', 'codigo', 'hora_inicio', 'hora_fim', 'ativo', 'descricao', 'ordem']);

        return response()->json($turnos);
    }

    /**
     * Retorna os dados de um turno específico para API
     */
    public function showApi($id)
    {
        $turno = Turno::where('id', $id)->where('ativo', true);
        
        // Para super admins e suporte, filtrar pela escola da sessão se definida
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual')) {
                $turno->where('escola_id', session('escola_atual'));
            }
        } else {
            // Para usuários normais, filtrar por sua escola
            if (auth()->user()->escola_id) {
                $turno->where('escola_id', auth()->user()->escola_id);
            }
        }

        $turno = $turno->first();

        if (!$turno) {
            return response()->json(['error' => 'Turno não encontrado'], 404);
        }

        return response()->json($turno);
    }

    /**
     * Alterna o status (ativo/inativo) do turno especificado.
     */
    public function toggleStatus(Request $request, Turno $turno)
    {
        // Autorizar apenas turnos da escola atual/usuário
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            if (session('escola_atual') && $turno->escola_id !== session('escola_atual')) {
                abort(404);
            }
        } else {
            if (auth()->user()->escola_id && $turno->escola_id !== auth()->user()->escola_id) {
                abort(404);
            }
        }

        $turno->update(['ativo' => !$turno->ativo]);

        $status = $turno->ativo ? 'ativado' : 'inativado';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Turno {$status} com sucesso!",
                'turno' => $turno
            ]);
        }

        // Redireciona de volta para a página anterior para manter o contexto
        return redirect()->back()->with('success', "Turno {$status} com sucesso!");
    }
}
