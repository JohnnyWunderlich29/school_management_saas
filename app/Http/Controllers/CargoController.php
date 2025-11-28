<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use App\Models\Permissao;
use App\Models\Historico;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Services\AlertService;

class CargoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cargo::with('permissoes');
        
        // Sempre filtrar por escola - nunca mostrar cargos globais
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            // Para super admins, usar escola da sessão ou escola do usuário
            $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
            if ($escolaId) {
                $query->where('escola_id', $escolaId);
            } else {
                // Se não há escola definida, não mostrar nenhum cargo
                $query->where('escola_id', -1); // ID inexistente
            }
        } else {
            // Para usuários normais, filtrar por sua escola
            if (auth()->user()->escola_id) {
                $query->where('escola_id', auth()->user()->escola_id);
            } else {
                // Se usuário não tem escola, não mostrar nenhum cargo
                $query->where('escola_id', -1); // ID inexistente
            }
        }
        // Filtros adicionais
        if ($request->filled('nome')) {
            $query->where('nome', 'like', '%' . $request->input('nome') . '%');
        }
        if ($request->filled('ativo')) {
            $ativo = $request->input('ativo');
            if (in_array($ativo, ['0', '1', 0, 1], true)) {
                $query->where('ativo', (int) $ativo);
            }
        }
        if ($request->filled('tipo_cargo')) {
            $query->where('tipo_cargo', $request->input('tipo_cargo'));
        }

        // Ordenação dinâmica
        $allowedSorts = ['id', 'nome', 'ativo', 'created_at'];
        $sort = $request->input('sort', 'created_at');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }
        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $direction);

        $cargos = $query->paginate(15)->withQueryString();
        return view('cargos.index', compact('cargos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissoes = Permissao::where('ativo', true)->get();
        $financePerms = Permissao::whereIn('nome', ['recebimentos.ver', 'recorrencias.ver'])
            ->pluck('id')->toArray();
        $templates = [
            [
                'nome' => 'Professor Financeiro',
                'tipo' => 'professor',
                'permissoes' => $financePerms,
            ],
        ];
        return view('cargos.create', compact('permissoes', 'templates'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nome' => 'required|string|max:255|unique:cargos',
                'descricao' => 'nullable|string|max:500',
                'ativo' => 'boolean',
                'permissoes' => 'array',
                'permissoes.*' => 'exists:permissoes,id',
                'tipo_cargo' => 'required|in:professor,coordenador,administrador,outro'
            ]);

            $cargo = Cargo::create([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'ativo' => $request->boolean('ativo', true),
                'escola_id' => session('escola_atual') ?: auth()->user()->escola_id,
                'tipo_cargo' => $request->tipo_cargo,
            ]);

            if ($request->has('permissoes')) {
                $cargo->permissoes()->attach($request->permissoes);
            }

            // Registrar no histórico
            Historico::registrar(
                'criado',
                'Cargo',
                $cargo->id,
                null,
                $cargo->fresh()->toArray(),
                'Cargo criado com sucesso'
            );

            AlertService::success('Cargo criado com sucesso!');
            return redirect()->route('cargos.index');
        } catch (\Exception $e) {
            AlertService::systemError('Erro ao criar cargo', $e);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Cargo $cargo)
    {
        $cargo->load('permissoes', 'users');
        return view('cargos.show', compact('cargo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cargo $cargo)
    {
        $permissoes = Permissao::where('ativo', true)->get();
        $cargo->load('permissoes');
        $financePerms = Permissao::whereIn('nome', ['recebimentos.ver', 'recorrencias.ver'])
            ->pluck('id')->toArray();
        $templates = [
            [
                'nome' => 'Professor Financeiro',
                'tipo' => 'professor',
                'permissoes' => $financePerms,
            ],
        ];
        return view('cargos.edit', compact('cargo', 'permissoes', 'templates'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cargo $cargo)
    {
        try {
            $request->validate([
                'nome' => ['required', 'string', 'max:255', Rule::unique('cargos')->ignore($cargo->id)],
                'descricao' => 'nullable|string|max:500',
                'ativo' => 'boolean',
                'permissoes' => 'array',
                'permissoes.*' => 'exists:permissoes,id',
                'tipo_cargo' => 'required|in:professor,coordenador,administrador,outro'
            ]);

            $dadosAntigos = $cargo->toArray();
            
            $cargo->update([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'ativo' => $request->boolean('ativo', true),
                'tipo_cargo' => $request->tipo_cargo,
            ]);

            // Sincronizar permissões
            if ($request->has('permissoes')) {
                $cargo->permissoes()->sync($request->permissoes);
            } else {
                $cargo->permissoes()->detach();
            }

            // Registrar no histórico
            Historico::registrar(
                'atualizado',
                'Cargo',
                $cargo->id,
                $dadosAntigos,
                $cargo->fresh()->toArray(),
                'Cargo atualizado com sucesso'
            );

            AlertService::success('Cargo atualizado com sucesso!');
            return redirect()->route('cargos.index');
        } catch (\Exception $e) {
            AlertService::systemError('Erro ao atualizar cargo', $e);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cargo $cargo)
    {
        try {
            // Verificar se o cargo tem usuários associados
            if ($cargo->users()->count() > 0) {
                AlertService::error('Não é possível excluir um cargo que possui usuários associados!');
                return redirect()->route('cargos.index');
            }

            $dadosAntigos = $cargo->toArray();
            
            // Remover associações com permissões
            $cargo->permissoes()->detach();
            
            // Excluir cargo
            $cargo->delete();

            // Registrar no histórico
            Historico::registrar(
                'excluido',
                'Cargo',
                $cargo->id,
                $dadosAntigos,
                null,
                'Cargo excluído com sucesso'
            );

            AlertService::success('Cargo excluído com sucesso!');
            return redirect()->route('cargos.index');
        } catch (\Exception $e) {
            AlertService::systemError('Erro ao excluir cargo', $e);
            return redirect()->back();
        }
    }
}
