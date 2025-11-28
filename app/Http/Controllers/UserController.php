<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Services\AlertService;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with('cargos');
        
        // Sempre filtrar por escola - nunca mostrar usuários globais
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            // Para super admins, usar escola da sessão ou escola do usuário
            $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
            if ($escolaId) {
                $query->where('escola_id', $escolaId);
            } else {
                // Se não há escola definida, não mostrar nenhum usuário
                $query->where('escola_id', -1); // ID inexistente
            }
        } else {
            // Para usuários normais, filtrar por sua escola
            if (auth()->user()->escola_id) {
                $query->where('escola_id', auth()->user()->escola_id);
            } else {
                // Se usuário não tem escola, não mostrar nenhum usuário
                $query->where('escola_id', -1); // ID inexistente
            }
        }
        
        // Se o usuário não é super admin, ocultar super administradores
        if (!auth()->user()->isSuperAdmin()) {
            $query->whereDoesntHave('cargos', function ($q) {
                $q->where('nome', 'Super Administrador');
            });
        }
        
        // Filtro por nome
        if ($request->filled('nome')) {
            $query->where('name', 'like', '%' . $request->nome . '%');
        }
        
        // Filtro por email
        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request->email . '%');
        }
        
        // Filtro por cargo
        if ($request->filled('cargo_id')) {
            $query->whereHas('cargos', function ($q) use ($request) {
                $q->where('cargo_id', $request->cargo_id);
            });
        }
        
        // Ordenação dinâmica
        $allowedSorts = [
            'id' => 'id',
            'name' => 'name',
            'email' => 'email',
            'created_at' => 'created_at',
        ];
        $sort = $request->input('sort', 'created_at');
        $direction = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        if (isset($allowedSorts[$sort])) {
            $query->orderBy($allowedSorts[$sort], $direction);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $users = $query->paginate(15)->appends($request->query());
        
        // Filtrar cargos por escola específica
        $cargos = Cargo::where('ativo', true);
        
        // Sempre filtrar por escola - nunca mostrar cargos globais
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
            if ($escolaId) {
                $cargos->where('escola_id', $escolaId);
            } else {
                $cargos->where('escola_id', -1); // ID inexistente
            }
        } else {
            if (auth()->user()->escola_id) {
                $cargos->where('escola_id', auth()->user()->escola_id);
            } else {
                $cargos->where('escola_id', -1); // ID inexistente
            }
        }
        
        $cargos = $cargos->get();
        
        // Filtrar cargo 'Super Administrador' - só mostrar para super administradores
        if (!auth()->user()->isSuperAdmin()) {
            $cargos = $cargos->filter(function($cargo) {
                return $cargo->nome !== 'Super Administrador';
            });
        }
        
        return view('usuarios.index', compact('users', 'cargos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Buscar apenas cargos específicos da escola atual
        $cargos = Cargo::where('ativo', true);
        
        // Sempre filtrar por escola - nunca mostrar cargos globais
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
            if ($escolaId) {
                $cargos->where('escola_id', $escolaId);
            } else {
                $cargos->where('escola_id', -1); // ID inexistente
            }
        } else {
            if (auth()->user()->escola_id) {
                $cargos->where('escola_id', auth()->user()->escola_id);
            } else {
                $cargos->where('escola_id', -1); // ID inexistente
            }
        }
        
        $cargos = $cargos->get();
        
        // Filtrar cargo 'Super Administrador' - só mostrar para super administradores
        if (!auth()->user()->isSuperAdmin()) {
            $cargos = $cargos->filter(function($cargo) {
                return $cargo->nome !== 'Super Administrador';
            });
        }
        
        $funcionarios = \App\Models\Funcionario::where('ativo', true)
            ->whereDoesntHave('user')
            ->orderBy('nome')
            ->get();
        return view('usuarios.create', compact('cargos', 'funcionarios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'funcionario_id' => 'required|exists:funcionarios,id',
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'cargos' => 'array',
                'cargos.*' => 'exists:cargos,id'
            ]);

            // Verificar se o funcionário já possui usuário
            $funcionario = \App\Models\Funcionario::find($request->funcionario_id);
            if ($funcionario->user_id) {
                AlertService::error('Este funcionário já possui um usuário vinculado!');
                return redirect()->back()->withInput();
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'escola_id' => session('escola_atual') ?: auth()->user()->escola_id,
            ]);

            // Vincular o usuário ao funcionário
            $funcionario->update(['user_id' => $user->id]);

            if ($request->has('cargos')) {
                $user->cargos()->attach($request->cargos);
            }

            AlertService::success('Usuário criado e vinculado ao funcionário com sucesso!');
            return redirect()->route('usuarios.index');
        } catch (\Exception $e) {
            AlertService::systemError('Erro ao criar usuário', $e);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load('cargos.permissoes');
        return view('usuarios.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        // Determinar escola_id para filtros (seguindo padrão do método index)
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
        } else {
            $escolaId = auth()->user()->escola_id;
        }
        
        // Buscar cargos globais (escola_id null) e cargos específicos da escola atual
        $cargos = Cargo::where('ativo', true)
            ->where(function($query) use ($escolaId) {
                $query->whereNull('escola_id') // Cargos globais/padrão do sistema
                      ->orWhere('escola_id', $escolaId); // Cargos específicos da escola
            })
            ->get();
        
        // Filtrar cargo 'Super Administrador' - só mostrar para super administradores
        if (!auth()->user()->isSuperAdmin()) {
            $cargos = $cargos->filter(function($cargo) {
                return $cargo->nome !== 'Super Administrador';
            });
        }
        
        $user->load('cargos');
        return view('usuarios.edit', compact('user', 'cargos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'password' => 'nullable|string|min:8|confirmed',
                'cargos' => 'array',
                'cargos.*' => 'exists:cargos,id'
            ]);

            $userData = [
                'name' => $request->name,
                'email' => $request->email,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            // Sincronizar cargos
            if ($request->has('cargos')) {
                $user->cargos()->sync($request->cargos);
            } else {
                $user->cargos()->detach();
            }

            AlertService::success('Usuário atualizado com sucesso!');
            return redirect()->route('usuarios.index');
        } catch (\Exception $e) {
            AlertService::systemError('Erro ao atualizar usuário', $e);
            return redirect()->back()->withInput();
        }
    }

    /**
     * Toggle user status (activate/deactivate)
     */
    public function toggleStatus(User $user)
    {
        try {
            // Verificar se não é o próprio usuário logado
            if (auth()->id() === $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não pode alterar o status da sua própria conta!'
                ], 400);
            }

            // Alternar status
            $user->ativo = !$user->ativo;
            $user->save();

            // Registrar no histórico
            \App\Models\Historico::create([
                'user_id' => auth()->id(),
                'escola_id' => auth()->user()->escola_id,
                'acao' => $user->ativo ? 'Usuário ativado' : 'Usuário inativado',
                'detalhes' => 'Usuário: ' . $user->name . ' (' . $user->email . ')'
            ]);

            $status = $user->ativo ? 'ativado' : 'inativado';
            return response()->json([
                'success' => true,
                'message' => "Usuário {$status} com sucesso!",
                'ativo' => $user->ativo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar status do usuário: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            // Verificar se não é o próprio usuário logado
            if (auth()->id() === $user->id) {
                AlertService::error('Você não pode excluir sua própria conta!');
                return redirect()->route('usuarios.index');
            }

            // Remover associações com cargos
            $user->cargos()->detach();
            
            // Excluir usuário
            $user->delete();

            AlertService::success('Usuário excluído com sucesso!');
            return redirect()->route('usuarios.index');
        } catch (\Exception $e) {
            AlertService::systemError('Erro ao excluir usuário', $e);
            return redirect()->back();
        }
    }
}
