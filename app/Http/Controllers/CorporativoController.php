<?php

namespace App\Http\Controllers;

use App\Models\Escola;
use App\Models\User;
use App\Models\Cargo;
use App\Models\Permissao;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CorporativoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['showLogin', 'login']);
    }

    /**
     * Exibe a página de login do painel corporativo
     */
    public function showLogin(): View
    {
        return view('corporativo.auth.login');
    }

    /**
     * Processa o login do painel corporativo
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Verificar se o usuário tem permissão para acessar o painel corporativo
            if ($user->isSuperAdmin() || $user->temCargo('Suporte')) {
                $request->session()->regenerate();
                return redirect()->route('corporativo.dashboard');
            } else {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Você não tem permissão para acessar o painel corporativo.',
                ]);
            }
        }

        return back()->withErrors([
            'email' => 'As credenciais fornecidas não conferem com nossos registros.',
        ]);
    }

    /**
     * Exibe o dashboard corporativo
     */
    public function dashboard(): View
    {
        // Estatísticas gerais - passando as variáveis que a view espera
        $totalUsers = User::count();
        $activeUsers = User::where('ativo', true)->count();
        $totalPermissions = Permissao::count();
        $totalRoles = Cargo::count();
        
        $stats = [
            'total_escolas' => Escola::count(),
            'escolas_ativas' => Escola::where('ativo', true)->count(),
            'escolas_inativas' => Escola::where('ativo', false)->count(),
            'total_usuarios' => $totalUsers,
            'usuarios_ativos' => $activeUsers,
            'usuarios_inativos' => User::where('ativo', false)->count(),
            'total_alunos' => \App\Models\Aluno::count(),
            'total_funcionarios' => \App\Models\Funcionario::count(),
            'receita_mensal' => Escola::where('em_dia', true)->sum('valor_mensalidade'),
            'valor_inadimplencia' => Escola::where('em_dia', false)->sum('valor_mensalidade'),
        ];

        // Dados para gráficos
        $crescimentoMensal = $this->getGrowthData();
        $escolasPorPlano = Escola::select('plano', DB::raw('count(*) as total'))
                                ->groupBy('plano')
                                ->get();

        return view('corporativo.dashboard', compact('stats', 'crescimentoMensal', 'escolasPorPlano', 'totalUsers', 'activeUsers', 'totalPermissions', 'totalRoles'));
    }

    /**
     * Exibe a página de gerenciamento de escolas
     */
    public function escolas(): View
    {
        $escolas = Escola::withCount(['users', 'funcionarios'])
                        ->orderBy('nome')
                        ->paginate(10);

        $stats = [
            'total_escolas' => Escola::count(),
            'escolas_ativas' => Escola::where('ativo', true)->count(),
            'escolas_inativas' => Escola::where('ativo', false)->count(),
            'escolas_em_dia' => Escola::where('em_dia', true)->count(),
            'escolas_inadimplentes' => Escola::where('em_dia', false)->count(),
            'por_plano' => [
                'basico' => Escola::where('plano', 'basico')->count(),
                'premium' => Escola::where('plano', 'premium')->count(),
                'enterprise' => Escola::where('plano', 'enterprise')->count(),
            ]
        ];
        
        return view('corporativo.escolas.index', compact('escolas', 'stats'));
    }

    /**
     * Exibe detalhes de uma escola específica
     */
    public function escolaDetalhes($id): View
    {
        $escola = Escola::with(['users', 'funcionarios', 'salas'])
                       ->withCount(['users', 'funcionarios'])
                       ->findOrFail($id);
        return view('corporativo.escolas.detalhes', compact('escola'));
    }

    /**
     * Exibe a página de gerenciamento de usuários
     */
    public function users(Request $request): View
    {
        $query = User::with(['cargos', 'escola']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('cargo_id')) {
            $query->whereHas('cargos', function($q) use ($request) {
                $q->where('cargos.id', $request->cargo_id);
            });
        }

        if ($request->filled('escola_id')) {
            $query->where('escola_id', $request->escola_id);
        }

        if ($request->filled('ativo')) {
            $query->where('ativo', $request->ativo === 'true');
        }

        // Ocultar Super Administradores para não-super admins
        if (!auth()->user()->isSuperAdmin()) {
            $query->whereDoesntHave('cargos', function($q) {
                $q->where('nome', 'Super Administrador');
            });
        }

        $users = $query->orderBy('name')->paginate(15);

        // Dados para filtros
        $cargos = Cargo::all();
        $escolas = Escola::orderBy('nome')->get();

        // Estatísticas
        $stats = [
            'total' => User::count(),
            'ativos' => User::where('ativo', true)->count(),
            'inativos' => User::where('ativo', false)->count(),
            'com_escola' => User::whereNotNull('escola_id')->count(),
            'sem_escola' => User::whereNull('escola_id')->count(),
        ];

        return view('corporativo.users.index', compact('users', 'cargos', 'escolas', 'stats'));
    }

    /**
     * Exibe formulário de edição de usuário no painel corporativo
     */
    public function editUser(Request $request, User $user)
    {
        $cargosQuery = Cargo::query();
        if (!auth()->user()->isSuperAdmin()) {
            // Ocultar cargos sensíveis para não super administradores
            $cargosQuery->whereNotIn('nome', ['Super Administrador', 'Suporte', 'Suporte Técnico']);
        }
        $cargos = $cargosQuery->get();
        $escolas = Escola::orderBy('nome')->get();

        // Se for requisição de modal/partial, retornar apenas o formulário
        if ($request->ajax() || $request->boolean('partial') || $request->boolean('modal')) {
            return response()->view('corporativo.users._form', compact('user', 'cargos', 'escolas'));
        }

        return view('corporativo.users.edit', compact('user', 'cargos', 'escolas'));
    }

    /**
     * Atualiza usuário no painel corporativo
     */
    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $isSuperAdmin = auth()->user()->isSuperAdmin();

        // Carregar IDs de cargos sensíveis
        $sensitiveRoleIds = Cargo::whereIn('nome', ['Super Administrador', 'Suporte', 'Suporte Técnico'])
            ->pluck('id')->toArray();

        // Validação com regras condicionais
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'escola_id' => ['nullable', 'exists:escolas,id'],
            'cargos' => ['array'],
            'cargos.*' => ['exists:cargos,id'],
            'ativo' => ['sometimes', 'boolean'],
        ];

        if ($isSuperAdmin) {
            $rules['password'] = ['nullable', 'string', 'min:8', 'confirmed'];
        } else {
            // Proibir alteração de senha por não super admins
            $rules['password'] = ['prohibited'];
            // Impedir seleção de cargos sensíveis
            if (!empty($sensitiveRoleIds)) {
                $rules['cargos.*'][] = Rule::notIn($sensitiveRoleIds);
            }
        }

        $validated = $request->validate($rules);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'escola_id' => $request->escola_id,
            'ativo' => $request->has('ativo'),
        ];

        if ($isSuperAdmin && $request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->has('cargos')) {
            $cargos = $request->cargos ?? [];
            // Filtrar cargos sensíveis se não for super admin (defesa adicional)
            if (!$isSuperAdmin && !empty($sensitiveRoleIds)) {
                $cargos = array_values(array_diff($cargos, $sensitiveRoleIds));
            }
            $user->cargos()->sync($cargos);
        } else {
            $user->cargos()->detach();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso!',
                'user' => $user->fresh(['escola', 'cargos'])
            ]);
        }

        return redirect()->route('corporativo.users')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Formulário de criação de usuário (apenas Super Admin)
     */
    public function createUser(Request $request)
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $cargos = Cargo::orderBy('nome')->get();
        $escolas = Escola::orderBy('nome')->get();

        if ($request->ajax() || $request->boolean('partial') || $request->boolean('modal')) {
            return response()->view('corporativo.users._form', compact('cargos', 'escolas'));
        }

        return view('corporativo.users.create', compact('cargos', 'escolas'));
    }

    /**
     * Cria usuário (apenas Super Admin)
     */
    public function storeUser(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'escola_id' => ['nullable', 'exists:escolas,id'],
            'cargos' => ['array'],
            'cargos.*' => ['exists:cargos,id'],
            'ativo' => ['sometimes', 'boolean'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'escola_id' => $data['escola_id'] ?? null,
            'ativo' => $request->has('ativo'),
        ]);

        if (!empty($data['cargos'])) {
            $user->cargos()->sync($data['cargos']);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso!',
                'user' => $user->fresh(['escola', 'cargos'])
            ]);
        }

        return redirect()->route('corporativo.users')->with('success', 'Usuário criado com sucesso!');
    }

    /**
     * Exibe a página de gerenciamento de permissões
     */
    public function permissions(Request $request): View
    {
        $query = Permissao::with('cargos')->withCount('cargos');

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%");
            });
        }

        if ($request->filled('modulo')) {
            $query->where('modulo', $request->modulo);
        }

        $permissions = $query->orderBy('modulo')->orderBy('nome')->paginate(15);

        // Estatísticas
        $stats = [
            'total' => Permissao::count(),
            'por_modulo' => Permissao::select('modulo', DB::raw('count(*) as total'))
                                   ->groupBy('modulo')
                                   ->orderBy('modulo')
                                   ->get(),
            'mais_usadas' => Permissao::withCount('cargos')
                                    ->orderBy('cargos_count', 'desc')
                                    ->limit(5)
                                    ->get(),
        ];

        // Módulos para filtro
        $modulos = Permissao::distinct()->pluck('modulo')->sort();

        return view('corporativo.permissions.index', compact('permissions', 'stats', 'modulos'));
    }

    /**
     * API: Retorna cargos vinculados a uma permissão (JSON)
     */
    public function getPermissionRoles(int $id)
    {
        try {
            $permission = Permissao::find($id);
            if (!$permission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permissão não encontrada'
                ], 404);
            }

            $roles = $permission->cargos()
                ->select('cargos.id', 'cargos.nome')
                ->withCount('users')
                ->orderBy('cargos.nome')
                ->get()
                ->map(function ($cargo) {
                    return [
                        'id' => $cargo->id,
                        'nome' => $cargo->nome,
                        'users_count' => $cargo->users_count ?? 0,
                    ];
                });

            return response()->json([
                'success' => true,
                'permission' => [
                    'id' => $permission->id,
                    'nome' => $permission->nome,
                ],
                'roles' => $roles,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar cargos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Exibe relatórios consolidados
     */
    public function relatorios(Request $request): View
    {
        // Base de cálculo selecionada
        $base = $request->query('base', 'estimado'); // estimado|plano|modulos|mensalidade

        // Calcula receita conforme base selecionada
        $todasEscolas = Escola::where('ativo', true)->get();
        $escolasEmDia = $todasEscolas->where('em_dia', true);
        $escolasInadimplentes = $todasEscolas->where('em_dia', false);

        $calcularMensal = function(Escola $escola) use ($base) {
            switch ($base) {
                case 'plano':
                    return (float) $escola->getPlanoPreco();
                case 'modulos':
                    return (float) $escola->getTotalModulesPrice();
                case 'mensalidade':
                    return (float) ($escola->valor_mensalidade ?? 0);
                case 'estimado':
                default:
                    return (float) ($escola->getPlanoPreco() + $escola->getTotalModulesPrice());
            }
        };

        $receitaPaga = $escolasEmDia->sum(function($e) use ($calcularMensal) {
            return $calcularMensal($e);
        });

        $valorInadimplencia = $escolasInadimplentes->sum(function($e) use ($calcularMensal) {
            return $calcularMensal($e);
        });

        $dados = [
            'usuarios_por_escola' => Escola::withCount('users')
                                          ->orderBy('users_count', 'desc')
                                          ->limit(10)
                                          ->get(),
            'crescimento_mensal' => $this->getGrowthData(),
            'receita_total' => $receitaPaga,
        ];
        
        // Estatísticas para os cards
        $stats = [
            'total_escolas' => Escola::count(),
            'escolas_ativas' => Escola::where('ativo', true)->count(),
            'escolas_em_dia' => Escola::where('em_dia', true)->count(),
            'escolas_inadimplentes' => Escola::where('em_dia', false)->count(),
            'total_usuarios' => User::count(),
            'novos_usuarios_mes' => User::whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)
                                       ->count(),
            'total_alunos' => \App\Models\Aluno::count(),
            'novos_alunos_mes' => \App\Models\Aluno::whereMonth('created_at', now()->month)
                                                   ->whereYear('created_at', now()->year)
                                                   ->count(),
            'receita_mensal' => $receitaPaga,
            'valor_inadimplencia' => $valorInadimplencia,
            'crescimento_receita' => 12.5, // Placeholder - calcular crescimento real
            // Distribuição por plano: total de escolas e receita prevista (ativo = true),
            // somando plano + módulos ativos
            'por_plano' => (function() use ($todasEscolas, $calcularMensal) {
                $grouped = $todasEscolas->groupBy('plano');
                $resultado = [];
                foreach ($grouped as $plano => $colecao) {
                    $resultado[$plano] = (object) [
                        'total' => $colecao->count(),
                        'receita' => $colecao->sum(function($e) use ($calcularMensal) {
                            return $calcularMensal($e);
                        }),
                    ];
                }
                return $resultado;
            })(),
            'top_usuarios' => Escola::withCount('users')
                                   ->orderByDesc('users_count')
                                   ->limit(5)
                                   ->get(),
            'escolas_recentes' => Escola::orderBy('created_at', 'desc')
                                       ->limit(5)
                                       ->get()
        ];
        
        // Buscar todas as escolas para o filtro
        $escolas = Escola::orderBy('nome')->get();

        return view('corporativo.relatorios.index', [
            'dados' => $dados,
            'escolas' => $escolas,
            'stats' => $stats,
            'base' => $base,
        ]);
    }

    /**
     * Executa consultas simples no banco de dados
     */
    public function showQueryBuilder(Request $request): View
    {
        // Obter lista de tabelas (compatível com SQLite, MySQL e PostgreSQL)
        $tableNames = [];
        try {
            if (config('database.default') === 'sqlite') {
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                foreach ($tables as $table) {
                    $tableNames[] = $table->name;
                }
            } elseif (config('database.default') === 'pgsql') {
                $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
                foreach ($tables as $table) {
                    $tableNames[] = $table->tablename;
                }
            } else {
                $tables = DB::select('SHOW TABLES');
                foreach ($tables as $table) {
                    $tableArray = (array) $table;
                    $tableNames[] = reset($tableArray);
                }
            }
        } catch (\Exception $e) {
            $tableNames = ['users', 'cargos', 'permissoes', 'funcionarios']; // Fallback
        }

        $user = auth()->user();
        
        // Obter histórico recente
        $recentHistoryQuery = \App\Models\QueryHistory::where('user_id', $user->id);
        
        // Super admins e suporte veem histórico de todas as escolas
        if (!($user->isSuperAdmin() || $user->temCargo('Suporte'))) {
            $recentHistoryQuery->where('escola_id', $user->escola_id);
        }
        
        $recentHistory = $recentHistoryQuery->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Obter favoritos
        $favoritesQuery = \App\Models\QueryFavorite::where('user_id', $user->id);
        
        // Super admins e suporte veem favoritos de todas as escolas
        if (!($user->isSuperAdmin() || $user->temCargo('Suporte'))) {
            $favoritesQuery->where('escola_id', $user->escola_id);
        }
        
        $favorites = $favoritesQuery->orderBy('name')
            ->get();

        return view('corporativo.query-builder.index', compact(
            'tableNames', 'recentHistory', 'favorites'
        ));
    }

    /**
     * Exibir informações do sistema
     */
    public function systemInfo(): View
    {
        // Coletar tabelas e extensões PHP para atender às chaves esperadas pela view
        $tables = $this->getSystemTables();
        $phpExtensions = get_loaded_extensions();
        sort($phpExtensions, SORT_NATURAL | SORT_FLAG_CASE);

        $systemInfo = [
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'database_driver' => config('database.default'),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'memory_limit' => ini_get('memory_limit'),
            'disk_space' => @disk_free_space('/') ?: 0,
            'disk_total' => @disk_total_space('/') ?: 0,
            // Chaves usadas pela view corporativo/system-info
            'tables' => $tables,
            'php_extensions' => $phpExtensions,
        ];

        $databaseInfo = [
            'tables_count' => count($tables),
            'connection_status' => 'connected',
        ];

        return view('corporativo.system-info.index', compact('systemInfo', 'databaseInfo'));
    }

    /**
     * Exibir relacionamentos entre tabelas do banco
     */
    public function databaseRelationships(): View
    {
        $relationships = $this->getDatabaseRelationships();
        $tables = $this->getSystemTables();
        
        return view('corporativo.database.relationships', compact('relationships', 'tables'));
    }

    /**
     * Logout do painel corporativo
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('corporativo.login');
    }

    /**
     * Métodos auxiliares privados
     */
    private function getGrowthData(): array
    {
        $months = [];
        $escolas = [];
        $usuarios = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M/Y');
            
            $escolas[] = Escola::whereYear('created_at', $date->year)
                              ->whereMonth('created_at', $date->month)
                              ->count();
                              
            $usuarios[] = User::whereYear('created_at', $date->year)
                             ->whereMonth('created_at', $date->month)
                             ->count();
        }
        
        return [
            'months' => $months,
            'escolas' => $escolas,
            'usuarios' => $usuarios,
        ];
    }

    private function getSystemTables(): array
    {
        try {
            if (config('database.default') === 'sqlite') {
                $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                return array_map(function($table) {
                    return $table->name;
                }, $tables);
            } elseif (config('database.default') === 'pgsql') {
                $tables = DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
                return array_map(function($table) {
                    return $table->tablename;
                }, $tables);
            } else {
                $tables = DB::select('SHOW TABLES');
                return array_map(function($table) {
                    $tableArray = (array) $table;
                    return reset($tableArray);
                }, $tables);
            }
        } catch (\Exception $e) {
            return ['users', 'escolas', 'cargos', 'permissoes', 'funcionarios', 'alunos'];
        }
    }

    private function getDatabaseRelationships(): array
    {
        $relationships = [];
        
        try {
            if (config('database.default') === 'sqlite') {
                // Para SQLite, analisar foreign keys
                $tables = $this->getSystemTables();
                
                foreach ($tables as $table) {
                    $foreignKeys = DB::select("PRAGMA foreign_key_list({$table})");
                    
                    foreach ($foreignKeys as $fk) {
                        $relationships[] = [
                            'from_table' => $table,
                            'from_column' => $fk->from,
                            'to_table' => $fk->table,
                            'to_column' => $fk->to,
                            'constraint_name' => "fk_{$table}_{$fk->from}",
                            'relationship_type' => 'foreign_key'
                        ];
                    }
                }
            } elseif (config('database.default') === 'pgsql') {
                // Para PostgreSQL, usar INFORMATION_SCHEMA
                $foreignKeys = DB::select("
                    SELECT 
                        kcu.table_name as from_table,
                        kcu.column_name as from_column,
                        ccu.table_name as to_table,
                        ccu.column_name as to_column,
                        tc.constraint_name as constraint_name
                    FROM 
                        information_schema.table_constraints tc
                        JOIN information_schema.key_column_usage kcu 
                            ON tc.constraint_name = kcu.constraint_name
                        JOIN information_schema.constraint_column_usage ccu 
                            ON ccu.constraint_name = tc.constraint_name
                    WHERE 
                        tc.constraint_type = 'FOREIGN KEY'
                        AND tc.table_schema = 'public'
                    ORDER BY 
                        kcu.table_name, kcu.column_name
                ");
                
                foreach ($foreignKeys as $fk) {
                    $relationships[] = [
                        'from_table' => $fk->from_table,
                        'from_column' => $fk->from_column,
                        'to_table' => $fk->to_table,
                        'to_column' => $fk->to_column,
                        'constraint_name' => $fk->constraint_name,
                        'relationship_type' => 'foreign_key'
                    ];
                }
            } else {
                // Para MySQL, usar INFORMATION_SCHEMA
                $database = config('database.connections.mysql.database');
                
                $foreignKeys = DB::select("
                    SELECT 
                        kcu.TABLE_NAME as from_table,
                        kcu.COLUMN_NAME as from_column,
                        kcu.REFERENCED_TABLE_NAME as to_table,
                        kcu.REFERENCED_COLUMN_NAME as to_column,
                        kcu.CONSTRAINT_NAME as constraint_name
                    FROM 
                        INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu
                    WHERE 
                        kcu.TABLE_SCHEMA = ? 
                        AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
                    ORDER BY 
                        kcu.TABLE_NAME, kcu.COLUMN_NAME
                ", [$database]);
                
                foreach ($foreignKeys as $fk) {
                    $relationships[] = [
                        'from_table' => $fk->from_table,
                        'from_column' => $fk->from_column,
                        'to_table' => $fk->to_table,
                        'to_column' => $fk->to_column,
                        'constraint_name' => $fk->constraint_name,
                        'relationship_type' => 'foreign_key'
                    ];
                }
            }
            
            // Adicionar relacionamentos inferidos baseados em convenções do Laravel
            $inferredRelationships = $this->getInferredRelationships();
            $relationships = array_merge($relationships, $inferredRelationships);
            
        } catch (\Exception $e) {
            // Fallback com relacionamentos conhecidos do sistema
            $relationships = $this->getFallbackRelationships();
        }
        
        return $relationships;
    }

    private function getInferredRelationships(): array
    {
        $relationships = [];
        $tables = $this->getSystemTables();
        
        foreach ($tables as $table) {
            try {
                $columns = [];
                
                if (config('database.default') === 'sqlite') {
                    $tableInfo = DB::select("PRAGMA table_info({$table})");
                    foreach ($tableInfo as $column) {
                        $columns[] = $column->name;
                    }
                } elseif (config('database.default') === 'pgsql') {
                    $tableInfo = DB::select("
                        SELECT column_name 
                        FROM information_schema.columns 
                        WHERE table_name = ? AND table_schema = 'public'
                        ORDER BY ordinal_position
                    ", [$table]);
                    foreach ($tableInfo as $column) {
                        $columns[] = $column->column_name;
                    }
                } else {
                    $tableInfo = DB::select("DESCRIBE {$table}");
                    foreach ($tableInfo as $column) {
                        $columns[] = $column->Field;
                    }
                }
                
                // Procurar por colunas que seguem convenção *_id
                foreach ($columns as $column) {
                    if (preg_match('/^(.+)_id$/', $column, $matches)) {
                        $referencedTable = $matches[1] . 's'; // Pluralizar
                        
                        // Verificar se a tabela referenciada existe
                        if (in_array($referencedTable, $tables)) {
                            $relationships[] = [
                                'from_table' => $table,
                                'from_column' => $column,
                                'to_table' => $referencedTable,
                                'to_column' => 'id',
                                'constraint_name' => "inferred_{$table}_{$column}",
                                'relationship_type' => 'inferred'
                            ];
                        }
                    }
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        return $relationships;
    }

    private function getFallbackRelationships(): array
    {
        return [
            [
                'from_table' => 'users',
                'from_column' => 'escola_id',
                'to_table' => 'escolas',
                'to_column' => 'id',
                'constraint_name' => 'fallback_users_escola_id',
                'relationship_type' => 'fallback'
            ],
            [
                'from_table' => 'funcionarios',
                'from_column' => 'escola_id',
                'to_table' => 'escolas',
                'to_column' => 'id',
                'constraint_name' => 'fallback_funcionarios_escola_id',
                'relationship_type' => 'fallback'
            ],
            [
                'from_table' => 'funcionarios',
                'from_column' => 'cargo_id',
                'to_table' => 'cargos',
                'to_column' => 'id',
                'constraint_name' => 'fallback_funcionarios_cargo_id',
                'relationship_type' => 'fallback'
            ],
            [
                'from_table' => 'cargo_permissao',
                'from_column' => 'cargo_id',
                'to_table' => 'cargos',
                'to_column' => 'id',
                'constraint_name' => 'fallback_cargo_permissao_cargo_id',
                'relationship_type' => 'fallback'
            ],
            [
                'from_table' => 'cargo_permissao',
                'from_column' => 'permissao_id',
                'to_table' => 'permissoes',
                'to_column' => 'id',
                'constraint_name' => 'fallback_cargo_permissao_permissao_id',
                'relationship_type' => 'fallback'
            ]
        ];
    }

    /**
     * Exibe a página de gerenciamento de licenças
     */
    public function licencas(Request $request): View
    {
        // Buscar todas as escolas com suas licenças
        $escolas = \App\Models\Escola::with(['licenses' => function($query) {
            $query->where('is_active', true)
                  ->where(function($q) {
                      $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                  });
        }])->get();

        // Estatísticas
        $stats = [
            'total_escolas' => \App\Models\Escola::count(),
            'escolas_ativas' => \App\Models\Escola::where('ativo', true)->count(),
            'licencas_ativas' => \App\Models\SchoolLicense::where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('expires_at')
                      ->orWhere('expires_at', '>', now());
                })->count(),
            'licencas_expiradas' => \App\Models\SchoolLicense::where('is_active', true)
                ->where('expires_at', '<=', now())->count()
        ];

        // Módulos disponíveis (inclui Relatórios)
        $modules = [
            'comunicacao_module' => 'Comunicação',
            'alunos_module' => 'Gestão de Alunos',
            'funcionarios_module' => 'Gestão de Funcionários',
            'academico_module' => 'Acadêmico',
            'administracao_module' => 'Administração',
            'relatorios_module' => 'Relatórios',
            'financeiro_module' => 'Financeiro',
            'biblioteca_module' => 'Biblioteca',
            'transporte_module' => 'Transporte'
        ];

        return view('corporativo.licencas.index', compact('escolas', 'stats', 'modules'));
    }

    /**
     * Obter relacionamentos entre tabelas via API
     */
    public function getDatabaseRelationshipsApi(): \Illuminate\Http\JsonResponse
    {
        try {
            $relationships = $this->getDatabaseRelationships();
            
            return response()->json([
                'success' => true,
                'data' => $relationships
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter relacionamentos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Executar consultas do Query Builder
     */
    public function executeQueryBuilder(Request $request): \Illuminate\Http\JsonResponse
    {
        $result = null;
        $error = null;
        $query = $request->get('query', '');
        $executionTime = null;
        $rowsReturned = null;

        if ($request->filled('query')) {
            $startTime = microtime(true);
            try {
                $user = auth()->user();
                $isSuperAdmin = $user && method_exists($user, 'isSuperAdmin') ? $user->isSuperAdmin() : false;

                $trimmedQueryOriginal = trim($query);
                $trimmedQueryUpper = strtoupper($trimmedQueryOriginal);
                \Log::info('Query Builder - Query recebida: ' . $query);

                // Permitir somente SELECT por padrão; Super Admin pode executar DELETE e ALTER
                $isSelect = str_starts_with($trimmedQueryUpper, 'SELECT');
                $isDelete = str_starts_with($trimmedQueryUpper, 'DELETE');
                $isAlter  = str_starts_with($trimmedQueryUpper, 'ALTER');

                // Auditoria: usuário, IP, operação e tabela alvo
                $operation = $isSelect ? 'SELECT' : ($isDelete ? 'DELETE' : ($isAlter ? 'ALTER' : 'OTHER'));
                $ip = $request->ip();
                $table = null;
                if ($isSelect && preg_match('/FROM\s+([A-Za-z0-9_]+)/i', $trimmedQueryOriginal, $m)) { $table = $m[1]; }
                elseif ($isDelete && preg_match('/DELETE\s+FROM\s+([A-Za-z0-9_]+)/i', $trimmedQueryOriginal, $m)) { $table = $m[1]; }
                elseif ($isAlter && preg_match('/ALTER\s+TABLE\s+([A-Za-z0-9_]+)/i', $trimmedQueryOriginal, $m)) { $table = $m[1]; }
                \Log::info('Query Builder - Auditoria', [
                    'user_id' => auth()->id(),
                    'user_email' => $user->email ?? null,
                    'ip' => $ip,
                    'operation' => $operation,
                    'table' => $table,
                    'requested_at' => now()->toDateTimeString(),
                ]);

                if (!$isSelect) {
                    if (!$isSuperAdmin || !($isDelete || $isAlter)) {
                        throw new \Exception('Apenas consultas SELECT são permitidas. Para DELETE/ALTER, requer perfil de Super Administrador.');
                    }
                    // Bloquear comandos perigosos mesmo para super admin
                    $blockedForAll = ['DROP', 'UPDATE', 'INSERT', 'CREATE', 'TRUNCATE'];
                    foreach ($blockedForAll as $cmd) {
                        if (str_contains($trimmedQueryUpper, $cmd)) {
                            throw new \Exception("Comando $cmd não é permitido pelo Query Builder.");
                        }
                    }
                    // Evitar múltiplas instruções encadeadas
                    if (substr_count($trimmedQueryOriginal, ';') > 1) {
                        throw new \Exception('Múltiplas instruções na mesma consulta não são permitidas.');
                    }
                }

                if ($isSelect) {
                    $result = \DB::select($query);
                    $rowsReturned = count($result);
                } elseif ($isDelete) {
                    // Executar DELETE e reportar afetados
                    $affected = \DB::affectingStatement($query);
                    $rowsReturned = (int) $affected; // armazenar como rows_returned para histórico
                } elseif ($isAlter) {
                    // Executar ALTER e retornar sucesso
                    \DB::statement($query);
                    $rowsReturned = 0;
                }

                $executionTime = round((microtime(true) - $startTime) * 1000, 2);

                // Salvar no histórico
                \App\Models\QueryHistory::create([
                    'user_id' => auth()->id(),
                    'escola_id' => $isSuperAdmin ? null : ($user ? $user->escola_id : null),
                    'query' => $query,
                    'description' => $request->get('description'),
                    'execution_time_ms' => (int) $executionTime,
                    'rows_returned' => $rowsReturned ?? 0,
                    'has_error' => false
                ]);

                return response()->json([
                    'success' => true,
                    'result' => $result,
                    'executionTime' => $executionTime,
                    'rowsReturned' => $rowsReturned,
                    'rowsAffected' => (!$isSelect) ? $rowsReturned : null,
                ]);

            } catch (\Exception $e) {
                $error = $e->getMessage();
                \Log::error('Query Builder - Erro na execução da consulta: ' . $error);
                $executionTime = round((microtime(true) - $startTime) * 1000, 2);

                // Salvar erro no histórico
                \App\Models\QueryHistory::create([
                    'user_id' => auth()->id(),
                    'escola_id' => auth()->user()->isSuperAdmin() ? null : auth()->user()->escola_id,
                    'query' => $query,
                    'description' => $request->get('description'),
                    'execution_time_ms' => (int) $executionTime,
                    'rows_returned' => 0,
                    'has_error' => true,
                    'error_message' => $error
                ]);

                return response()->json([
                    'success' => false,
                    'error' => $error,
                    'executionTime' => $executionTime
                ], 400);
            }
        }

        return response()->json([
            'success' => false,
            'error' => 'Nenhuma consulta fornecida.'
        ], 400);
    }

    /**
     * Obter colunas de uma tabela para joins
     */
    public function getTableColumns($tableName): \Illuminate\Http\JsonResponse
    {
        try {
            $columns = [];
            
            if (config('database.default') === 'sqlite') {
                $tableColumns = \DB::select("PRAGMA table_info({$tableName})");
                foreach ($tableColumns as $column) {
                    $columns[] = [
                        'name' => $column->name,
                        'type' => $column->type,
                        'nullable' => !$column->notnull,
                        'primary' => $column->pk
                    ];
                }
            } elseif (config('database.default') === 'pgsql') {
                $tableColumns = \DB::select("
                    SELECT 
                        c.column_name,
                        c.data_type,
                        c.is_nullable,
                        c.column_default,
                        CASE WHEN tc.constraint_type = 'PRIMARY KEY' THEN true ELSE false END as is_primary
                    FROM information_schema.columns c
                    LEFT JOIN information_schema.key_column_usage kcu 
                        ON c.table_name = kcu.table_name AND c.column_name = kcu.column_name AND c.table_schema = kcu.table_schema
                    LEFT JOIN information_schema.table_constraints tc 
                        ON kcu.constraint_name = tc.constraint_name AND tc.constraint_type = 'PRIMARY KEY' AND kcu.table_schema = tc.table_schema
                    WHERE c.table_name = ? AND c.table_schema = 'public'
                    ORDER BY c.ordinal_position
                ", [$tableName]);
                foreach ($tableColumns as $column) {
                    $columns[] = [
                        'name' => $column->column_name,
                        'type' => $column->data_type,
                        'nullable' => $column->is_nullable === 'YES',
                        'primary' => $column->is_primary
                    ];
                }
            } else {
                $tableColumns = \DB::select("DESCRIBE {$tableName}");
                foreach ($tableColumns as $column) {
                    $columns[] = [
                        'name' => $column->Field,
                        'type' => $column->Type,
                        'nullable' => $column->Null === 'YES',
                        'primary' => $column->Key === 'PRI'
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $columns
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter colunas da tabela: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter informações detalhadas de uma tabela (usado em /corporativo/system-info)
     */
    public function getTableInfo($tableName): \Illuminate\Http\JsonResponse
    {
        try {
            $tableInfo = [];

            if (config('database.default') === 'sqlite') {
                $columns = \DB::select("PRAGMA table_info({$tableName})");
                $indexes = \DB::select("PRAGMA index_list({$tableName})");

                $tableInfo = [
                    'name' => $tableName,
                    'engine' => 'SQLite',
                    'charset' => 'UTF-8',
                    'columns' => $columns,
                    'indexes' => $indexes,
                    'row_count' => \DB::table($tableName)->count(),
                ];
            } elseif (config('database.default') === 'pgsql') {
                // Normalizar colunas para chaves compatíveis com a view (usa col.name/type)
                $columns = \DB::select("
                    SELECT 
                        column_name as name,
                        data_type as type,
                        (is_nullable = 'NO') as notnull
                    FROM information_schema.columns 
                    WHERE table_name = ? AND table_schema = 'public'
                    ORDER BY ordinal_position
                ", [$tableName]);

                $tableInfo = [
                    'name' => $tableName,
                    'engine' => 'PostgreSQL',
                    'charset' => 'UTF-8',
                    'columns' => $columns,
                    'row_count' => \DB::table($tableName)->count(),
                ];
            } else {
                // MySQL/MariaDB
                $columns = \DB::select("DESCRIBE {$tableName}");
                $tableInfo = [
                    'name' => $tableName,
                    'engine' => 'InnoDB',
                    'charset' => 'utf8mb4_unicode_ci',
                    'columns' => $columns,
                    'row_count' => \DB::table($tableName)->count(),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $tableInfo
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter informações da tabela: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API para listar escolas com filtros
     */
    public function escolasApi(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $query = Escola::withCount(['users', 'funcionarios']);

            // Filtros
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nome', 'like', "%{$search}%")
                      ->orWhere('codigo', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $status = $request->status;
                if ($status === 'ativa') {
                    $query->where('ativo', true);
                } elseif ($status === 'inativa') {
                    $query->where('ativo', false);
                }
            }

            if ($request->filled('pagamento')) {
                $pagamento = $request->pagamento;
                if ($pagamento === 'em_dia') {
                    $query->where('em_dia', true);
                } elseif ($pagamento === 'inadimplente') {
                    $query->where('em_dia', false);
                }
            }

            // Ordenação
            $query->orderBy('nome');

            // Paginação
            $escolas = $query->paginate(10);

            // Formatar dados para o frontend
            $escolasFormatted = $escolas->map(function($escola) {
                return [
                    'id' => $escola->id,
                    'nome' => $escola->nome,
                    'codigo' => $escola->codigo,
                    'plano' => $escola->plano,
                    'valor_mensalidade' => $escola->valor_mensalidade,
                    'ativa' => $escola->ativo,
                    'pagamento_em_dia' => $escola->em_dia,
                    'usuarios_count' => $escola->users_count,
                    'funcionarios_count' => $escola->funcionarios_count,
                    'created_at' => $escola->created_at,
                ];
            });

            return response()->json([
                'success' => true,
                'escolas' => $escolasFormatted,
                'pagination' => [
                    'current_page' => $escolas->currentPage(),
                    'last_page' => $escolas->lastPage(),
                    'per_page' => $escolas->perPage(),
                    'total' => $escolas->total(),
                    'from' => $escolas->firstItem(),
                    'to' => $escolas->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar escolas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Página de KPIs do Corporativo
     */
    public function kpis(Request $request): View
    {
        $base = $request->query('base', 'estimado'); // estimado|plano|modulos|mensalidade
        $todasEscolasAtivas = Escola::where('ativo', true)->withCount(['users'])->get();
        $escolasPagantes = $todasEscolasAtivas->where('em_dia', true);
        $escolasInadimplentes = $todasEscolasAtivas->where('em_dia', false);

        $calcularMensal = function(Escola $escola) use ($base) {
            switch ($base) {
                case 'plano':
                    return (float) $escola->getPlanoPreco();
                case 'modulos':
                    return (float) $escola->getTotalModulesPrice();
                case 'mensalidade':
                    return (float) ($escola->valor_mensalidade ?? 0);
                case 'estimado':
                default:
                    return (float) ($escola->getPlanoPreco() + $escola->getTotalModulesPrice());
            }
        };

        $mrr = $escolasPagantes->sum(fn($e) => $calcularMensal($e));
        $mrrPotencial = $todasEscolasAtivas->sum(fn($e) => $calcularMensal($e));
        $arr = $mrr * 12;
        $pagantesCount = $escolasPagantes->count();
        $ativasCount = $todasEscolasAtivas->count();
        $inadimplentesCount = $escolasInadimplentes->count();

        $arpa = $pagantesCount > 0 ? round($mrr / $pagantesCount, 2) : 0;
        $payingRate = $ativasCount > 0 ? round(($pagantesCount / $ativasCount) * 100, 2) : 0;
        $inadimplenciaValor = $escolasInadimplentes->sum(fn($e) => $calcularMensal($e));
        $inadimplenciaRate = $ativasCount > 0 ? round(($inadimplentesCount / $ativasCount) * 100, 2) : 0;

        $avgUsersPorEscola = $ativasCount > 0 ? round($todasEscolasAtivas->avg('users_count'), 2) : 0;

        // KPIs adicionais (aproximações para demonstração)
        $basePagantesMaisInadimplentes = max(1, ($pagantesCount + $inadimplentesCount));
        $churnRateAprox = $ativasCount > 0
            ? round(($inadimplentesCount / $basePagantesMaisInadimplentes) * 100, 2)
            : 0;
        $ltvAprox = ($churnRateAprox > 0)
            ? round($arpa / ($churnRateAprox / 100), 2)
            : 0;

        // Distribuição por plano (ativas)
        $porPlano = [];
        foreach ($todasEscolasAtivas->groupBy('plano') as $plano => $colecao) {
            $porPlano[$plano ?? 'sem_plano'] = (object) [
                'total' => $colecao->count(),
                'receita' => $colecao->sum(fn($e) => $calcularMensal($e)),
            ];
        }

        // Adoção por módulo (ativos)
        $modules = \App\Models\Module::where('is_active', true)->get();
        $adocaoModulos = $modules->map(function($module) {
            $count = \App\Models\EscolaModule::where('module_id', $module->id)
                ->where('is_active', true)
                ->where(function($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->distinct('escola_id')
                ->count('escola_id');
            return [
                'id' => $module->id,
                'name' => $module->display_name ?? $module->name,
                'count' => $count,
            ];
        })->values();

        // MRR histórico (últimos 6 meses - aproximação por data de criação e status atual)
        $mrrHistorico = [
            'labels' => [],
            'values' => []
        ];
        for ($i = 5; $i >= 0; $i--) {
            $dt = now()->startOfMonth()->subMonths($i);
            $endOfMonth = (clone $dt)->endOfMonth();
            $label = $dt->format('M/Y');
            $subset = $todasEscolasAtivas->filter(fn($e) => $e->created_at <= $endOfMonth && $e->em_dia);
            $value = $subset->sum(fn($e) => $calcularMensal($e));
            $mrrHistorico['labels'][] = $label;
            $mrrHistorico['values'][] = round($value, 2);
        }

        // Churn histórico (aproximação: proporção de inadimplentes entre ativas em cada mês)
        $churnHistorico = [
            'labels' => [],
            'values' => []
        ];
        for ($i = 5; $i >= 0; $i--) {
            $dt = now()->startOfMonth()->subMonths($i);
            $endOfMonth = (clone $dt)->endOfMonth();
            $label = $dt->format('M/Y');
            $subsetAtivas = $todasEscolasAtivas->filter(fn($e) => $e->created_at <= $endOfMonth);
            $subsetPagantes = $subsetAtivas->filter(fn($e) => $e->em_dia);
            $subsetInadimplentes = $subsetAtivas->filter(fn($e) => !$e->em_dia);
            $base = max(1, ($subsetPagantes->count() + $subsetInadimplentes->count()));
            $rate = round(($subsetInadimplentes->count() / $base) * 100, 2);
            $churnHistorico['labels'][] = $label;
            $churnHistorico['values'][] = $rate;
        }

        // Cohort de ativação: por mês de criação (últimos 6 meses)
        $cohortAtivacao = [];
        for ($i = 5; $i >= 0; $i--) {
            $dt = now()->startOfMonth()->subMonths($i);
            $label = $dt->format('M/Y');
            $totalMes = Escola::whereYear('created_at', $dt->year)
                ->whereMonth('created_at', $dt->month)
                ->count();
            $pagantesMes = Escola::whereYear('created_at', $dt->year)
                ->whereMonth('created_at', $dt->month)
                ->where('ativo', true)
                ->where('em_dia', true)
                ->count();
            $rate = $totalMes > 0 ? round(($pagantesMes / $totalMes) * 100, 2) : 0;
            $cohortAtivacao[] = [
                'mes' => $label,
                'total' => $totalMes,
                'pagantes' => $pagantesMes,
                'rate' => $rate,
            ];
        }

        // Top escolas por receita estimada
        $topEscolas = $todasEscolasAtivas->map(function($e) use ($calcularMensal) {
                $e->receita_mensal_estimada = $calcularMensal($e);
                return $e;
            })
            ->sortByDesc('receita_mensal_estimada')
            ->take(10)
            ->values();

        $cards = [
            'mrr' => $mrr,
            'arr' => $arr,
            'arpa' => $arpa,
            'paying_rate' => $payingRate,
            'inadimplencia_valor' => $inadimplenciaValor,
            'inadimplencia_rate' => $inadimplenciaRate,
            'avg_users_por_escola' => $avgUsersPorEscola,
            'escolas_ativas' => $ativasCount,
            'escolas_pagantes' => $pagantesCount,
            'churn_rate_aprox' => $churnRateAprox,
            'ltv_aprox' => $ltvAprox,
        ];

        return view('corporativo.kpis.index', [
            'cards' => $cards,
            'por_plano' => $porPlano,
            'adocao_modulos' => $adocaoModulos,
            'mrr_historico' => $mrrHistorico,
            'churn_historico' => $churnHistorico,
            'cohort_ativacao' => $cohortAtivacao,
            'top_escolas' => $topEscolas,
            'base' => $base,
        ]);
    }
}