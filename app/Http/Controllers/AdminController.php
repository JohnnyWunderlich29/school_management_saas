<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Permissao;
use App\Models\Cargo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * API: Listar todas as permissões
     */
    public function apiPermissions(Request $request)
    {
        $query = \App\Models\Permissao::query();
        
        // Filtro por busca
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%")
                  ->orWhere('descricao', 'like', "%{$search}%")
                  ->orWhere('modulo', 'like', "%{$search}%");
            });
        }
        
        // Filtro por módulo
        if ($request->filled('modulo')) {
            $query->where('modulo', $request->get('modulo'));
        }
        
        // Filtro por status
        if ($request->filled('ativo')) {
            $query->where('ativo', $request->boolean('ativo'));
        }
        
        $permissions = $query->with('cargos:id,nome')
                            ->orderBy('modulo')
                            ->orderBy('nome')
                            ->paginate($request->get('per_page', 15));
        
        return response()->json($permissions);
    }

    /**
     * API: Criar nova permissão
     */
    public function apiCreatePermission(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:permissoes,nome',
            'modulo' => 'required|string|max:100',
            'descricao' => 'nullable|string|max:500',
            'ativo' => 'boolean'
        ]);
        
        $permission = \App\Models\Permissao::create([
            'nome' => $request->nome,
            'modulo' => $request->modulo,
            'descricao' => $request->descricao,
            'ativo' => $request->boolean('ativo', true)
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Permissão criada com sucesso!',
            'data' => $permission
        ], 201);
    }

    /**
     * API: Atualizar permissão
     */
    public function apiUpdatePermission(Request $request, $id)
    {
        $permission = \App\Models\Permissao::findOrFail($id);
        
        $request->validate([
            'nome' => 'required|string|max:255|unique:permissoes,nome,' . $id,
            'modulo' => 'required|string|max:100',
            'descricao' => 'nullable|string|max:500',
            'ativo' => 'boolean'
        ]);
        
        $permission->update([
            'nome' => $request->nome,
            'modulo' => $request->modulo,
            'descricao' => $request->descricao,
            'ativo' => $request->boolean('ativo', true)
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Permissão atualizada com sucesso!',
            'data' => $permission->fresh()
        ]);
    }

    /**
     * API: Excluir permissão
     */
    public function apiDeletePermission($id)
    {
        $permission = \App\Models\Permissao::findOrFail($id);
        
        // Verificar se a permissão está sendo usada
        if ($permission->cargos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível excluir esta permissão pois ela está sendo usada por um ou mais cargos.'
            ], 422);
        }
        
        $permission->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Permissão excluída com sucesso!'
        ]);
    }

    /**
     * API: Listar todos os usuários
     */
    public function apiUsers(Request $request)
    {
        $query = User::with(['cargos:id,nome', 'unidadeEscolar:id,nome']);
        
        // Filtro por busca
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        // Filtro por cargo
        if ($request->filled('cargo_id')) {
            $query->whereHas('cargos', function($q) use ($request) {
                $q->where('cargo_id', $request->get('cargo_id'));
            });
        }
        
        // Filtro por status
        if ($request->filled('ativo')) {
            $query->where('ativo', $request->boolean('ativo'));
        }
        
        // Filtro por escola
        if ($request->filled('escola_id')) {
            $query->where('escola_id', $request->get('escola_id'));
        }
        
        $users = $query->orderBy('name')
                      ->paginate($request->get('per_page', 15));
        
        return response()->json($users);
    }

    /**
     * API: Criar novo usuário
     */
    public function apiCreateUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'escola_id' => 'required|exists:escolas,id',
            'cargos' => 'array',
            'cargos.*' => 'exists:cargos,id',
            'ativo' => 'boolean'
        ]);
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Hash::make($request->password),
            'escola_id' => $request->escola_id,
            'ativo' => $request->boolean('ativo', true)
        ]);
        
        // Atribuir cargos se fornecidos
        if ($request->filled('cargos')) {
            $user->cargos()->attach($request->cargos);
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Usuário criado com sucesso!',
            'data' => $user->load('cargos')
        ], 201);
    }



    /**
     * API: Excluir usuário
     */
    public function apiDeleteUser($id)
    {
        $user = User::findOrFail($id);
        
        // Verificar se não é o próprio usuário logado
        if ($user->id === auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Você não pode excluir sua própria conta.'
            ], 422);
        }
        
        // Remover relacionamentos
        $user->cargos()->detach();
        $user->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Usuário excluído com sucesso!'
        ]);
    }

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
                // Apenas permitir consultas SELECT por segurança
                $trimmedQuery = trim(strtoupper($query));
                \Log::info('Query Builder - Query recebida: ' . $query);
                if (!str_starts_with($trimmedQuery, 'SELECT')) {
                    throw new \Exception('Apenas consultas SELECT são permitidas.');
                }

                $result = \DB::select($query);
                $executionTime = round((microtime(true) - $startTime) * 1000, 2);
                $rowsReturned = count($result);
                
                // Salvar no histórico
                \App\Models\QueryHistory::create([
                    'user_id' => auth()->id(),
                    'escola_id' => auth()->user()->isSuperAdmin() ? null : auth()->user()->escola_id,
                    'query' => $query,
                    'description' => $request->get('description'),
                    'execution_time_ms' => $executionTime,
                    'rows_returned' => $rowsReturned,
                    'has_error' => false
                ]);

                return response()->json([
                    'success' => true,
                    'result' => $result,
                    'executionTime' => $executionTime,
                    'rowsReturned' => $rowsReturned
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
                    'execution_time_ms' => $executionTime,
                    'rows_returned' => 0,
                    'has_error' => true,
                    'error_message' => $error
                ]);

                return response()->json([
                    'success' => false,
                    'error' => $error,
                    'executionTime' => $executionTime
                ], 422);
            }
        }

        return response()->json([
            'success' => false,
            'error' => 'Nenhuma consulta fornecida.',
            'executionTime' => 0
        ], 400);
    }
    
    /**
     * API: Salvar consulta como favorita
     */
    public function apiSaveFavoriteQuery(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'query' => 'required|string',
            'description' => 'nullable|string',
            'tags' => 'nullable|array'
        ]);
        
        $favorite = \App\Models\QueryFavorite::create([
            'user_id' => auth()->id(),
            'escola_id' => auth()->user()->isSuperAdmin() ? null : auth()->user()->escola_id,
            'name' => $request->name,
            'query' => $request->query,
            'description' => $request->description,
            'tags' => $request->tags ?? []
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Consulta salva como favorita!',
            'data' => $favorite
        ]);
    }
    
    /**
     * API: Excluir consulta favorita
     */
    public function apiDeleteFavoriteQuery($id)
    {
        $favorite = \App\Models\QueryFavorite::where('user_id', auth()->id())
            ->where('escola_id', auth()->user()->escola_id)
            ->findOrFail($id);
            
        $favorite->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Consulta favorita excluída!'
        ]);
    }
    
    /**
     * API: Exportar resultado da consulta
     */
    public function apiExportQuery(Request $request)
    {
        $request->validate([
            'query' => 'required|string',
            'format' => 'required|in:csv,json,excel'
        ]);
        
        try {
            // Apenas permitir consultas SELECT por segurança
            $trimmedQuery = trim(strtoupper($request->query));
            if (!str_starts_with($trimmedQuery, 'SELECT')) {
                throw new \Exception('Apenas consultas SELECT são permitidas.');
            }

            $result = \DB::select($request->query());
            $format = $request->format();
            
            if ($format === 'json') {
                return response()->json($result)
                    ->header('Content-Disposition', 'attachment; filename="query_result.json"');
            }

            if ($format === 'csv') {
                $filename = 'query_result_' . date('Y-m-d_H-i-s') . '.csv';
                $headers = [
                    'Content-Type' => 'text/csv',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"'
                ];
                
                $callback = function() use ($result) {
                    $file = fopen('php://output', 'w');
                    
                    if (!empty($result)) {
                        // Cabeçalhos
                        $firstRow = (array) $result[0];
                        fputcsv($file, array_keys($firstRow));
                        
                        // Dados
                        foreach ($result as $row) {
                            fputcsv($file, (array) $row);
                        }
                    }
                    
                    fclose($file);
                };
                
                return response()->stream($callback, 200, $headers);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar: ' . $e->getMessage()
            ], 422);
        }
    }
    
    /**
     * API: Obter histórico de consultas
     */
    public function apiQueryHistory(Request $request)
    {
        $query = \App\Models\QueryHistory::where('user_id', auth()->id())
            ->where('escola_id', auth()->user()->escola_id)
            ->orderBy('created_at', 'desc');
            
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('query', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('has_error')) {
            $query->where('has_error', $request->boolean('has_error'));
        }
        
        $history = $query->paginate($request->get('per_page', 20));
        
        return response()->json($history);
    }

    /**
     * Exibe detalhes de uma escola específica
     */
    public function escolaDetalhes($id): View
    {

        $escola = \App\Models\Escola::with(['users', 'funcionarios', 'salas'])
                                   ->withCount(['users', 'funcionarios'])
                                   ->findOrFail($id);
        return view('admin.escola-detalhes', compact('escola'));
    }

    /**
     * Obter dados de crescimento mensal
     */
    private function getGrowthData()
    {
        return \App\Models\Escola::select(
            DB::raw('strftime("%Y-%m", created_at) as mes'),
            DB::raw('count(*) as total')
        )
        ->where('created_at', '>=', now()->subMonths(12))
        ->groupBy('mes')
        ->orderBy('mes')
        ->get();
    }

    /**
     * Limpar cache do sistema
     */
    public function clearCache(): \Illuminate\Http\JsonResponse
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
            \Illuminate\Support\Facades\Artisan::call('route:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'Cache limpo com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao limpar cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Otimizar sistema
     */
    public function optimizeSystem(): \Illuminate\Http\JsonResponse
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('optimize');
            \Illuminate\Support\Facades\Artisan::call('config:cache');
            \Illuminate\Support\Facades\Artisan::call('route:cache');
            \Illuminate\Support\Facades\Artisan::call('view:cache');
            
            return response()->json([
                'success' => true,
                'message' => 'Sistema otimizado com sucesso!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao otimizar sistema: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Executar manutenção do sistema
     */
    public function runMaintenance(): \Illuminate\Http\JsonResponse
    {
        try {
            // Limpar logs antigos
            $logPath = storage_path('logs');
            $files = glob($logPath . '/*.log');
            $deletedFiles = 0;
            
            foreach ($files as $file) {
                if (filemtime($file) < strtotime('-30 days')) {
                    unlink($file);
                    $deletedFiles++;
                }
            }
            
            // Limpar sessões expiradas
            \Illuminate\Support\Facades\Artisan::call('session:gc');
            
            // Otimizar banco de dados (SQLite)
            if (config('database.default') === 'sqlite') {
                \DB::statement('VACUUM');
            }
            
            return response()->json([
                'success' => true,
                'message' => "Manutenção executada com sucesso! {$deletedFiles} arquivos de log antigos removidos."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar manutenção: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gerar relatório do sistema
     */
    public function generateReport(): \Illuminate\Http\JsonResponse
    {
        try {
            $report = [
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'system' => [
                    'laravel_version' => app()->version(),
                    'php_version' => PHP_VERSION,
                    'environment' => app()->environment(),
                    'debug_mode' => config('app.debug'),
                    'database_driver' => config('database.default'),
                ],
                'statistics' => [
                    'total_schools' => \App\Models\Escola::count(),
                    'active_schools' => \App\Models\Escola::where('ativo', true)->count(),
                    'total_users' => \App\Models\User::count(),
                    'total_students' => \App\Models\Aluno::count(),
                    'total_employees' => \App\Models\Funcionario::count(),
                ],
                'performance' => [
                    'memory_usage' => memory_get_usage(true),
                    'memory_peak' => memory_get_peak_usage(true),
                    'memory_limit' => ini_get('memory_limit'),
                ],
                'database' => [
                    'tables_count' => count($this->getSystemTables()),
                    'connection_status' => 'connected',
                ]
            ];
            
            // Salvar relatório em arquivo
            $filename = 'system_report_' . now()->format('Y_m_d_H_i_s') . '.json';
            $filepath = storage_path('app/reports/' . $filename);
            
            // Criar diretório se não existir
            if (!file_exists(dirname($filepath))) {
                mkdir(dirname($filepath), 0755, true);
            }
            
            file_put_contents($filepath, json_encode($report, JSON_PRETTY_PRINT));
            
            return response()->json([
                'success' => true,
                'message' => 'Relatório gerado com sucesso!',
                'filename' => $filename,
                'download_url' => route('admin.download-report', $filename)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download de relatório
     */
    public function downloadReport($filename)
    {
        $filepath = storage_path('app/reports/' . $filename);
        
        if (!file_exists($filepath)) {
            abort(404, 'Relatório não encontrado');
        }
        
        return response()->download($filepath);
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
     * Obter informações detalhadas de uma tabela
     */
    public function getTableInfo($tableName): \Illuminate\Http\JsonResponse
    {
        try {
            $tableInfo = [];
            
            if (config('database.default') === 'sqlite') {
                // Para SQLite
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
            } else {
                // Para MySQL
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
     * Obter lista de tabelas do sistema
     */
    private function getSystemTables(): array
    {
        $tableNames = [];
        try {
            if (config('database.default') === 'sqlite') {
                $tables = \DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
                foreach ($tables as $table) {
                    $tableNames[] = $table->name;
                }
            } else {
                $tables = \DB::select('SHOW TABLES');
                foreach ($tables as $table) {
                    $tableArray = (array) $table;
                    $tableNames[] = reset($tableArray);
                }
            }
        } catch (\Exception $e) {
            $tableNames = ['users', 'cargos', 'permissoes', 'funcionarios'];
        }
        
        return $tableNames;
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
     * Obter relacionamentos entre tabelas do banco de dados
     */
    private function getDatabaseRelationships(): array
    {
        $relationships = [];
        
        try {
            if (config('database.default') === 'sqlite') {
                // Para SQLite, analisar foreign keys
                $tables = $this->getSystemTables();
                
                foreach ($tables as $table) {
                    $foreignKeys = \DB::select("PRAGMA foreign_key_list({$table})");
                    
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
            } else {
                // Para MySQL, usar INFORMATION_SCHEMA
                $database = config('database.connections.mysql.database');
                
                $foreignKeys = \DB::select("
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

    /**
     * Obter relacionamentos inferidos baseados em convenções
     */
    private function getInferredRelationships(): array
    {
        $relationships = [];
        $tables = $this->getSystemTables();
        
        foreach ($tables as $table) {
            try {
                $columns = [];
                
                if (config('database.default') === 'sqlite') {
                    $tableInfo = \DB::select("PRAGMA table_info({$table})");
                    foreach ($tableInfo as $column) {
                        $columns[] = $column->name;
                    }
                } else {
                    $tableInfo = \DB::select("DESCRIBE {$table}");
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

    /**
     * Relacionamentos de fallback conhecidos do sistema
     */
    private function getFallbackRelationships(): array
    {
        return [
            [
                'from_table' => 'funcionarios',
                'from_column' => 'user_id',
                'to_table' => 'users',
                'to_column' => 'id',
                'constraint_name' => 'fallback_funcionarios_user_id',
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
     * Exibir formulário de edição de usuário
     */
    public function editUser(User $user)
    {
        // Filtrar cargos - Super Administrador só aparece para super administradores
        $cargos = \App\Models\Cargo::query();
        if (!auth()->user()->isSuperAdmin()) {
            $cargos->where('nome', '!=', 'Super Administrador');
        }
        $cargos = $cargos->get();
        
        $escolas = \App\Models\Escola::all();
        
        return view('admin.users.edit', compact('user', 'cargos', 'escolas'));
    }

    /**
     * Atualizar usuário
     */
    public function updateUser(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'escola_id' => 'nullable|exists:escolas,id',
            'cargos' => 'array',
            'cargos.*' => 'exists:cargos,id',
            'ativo' => 'boolean'
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'escola_id' => $request->escola_id,
            'ativo' => $request->has('ativo')
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => bcrypt($request->password)]);
        }

        if ($request->has('cargos')) {
            $user->cargos()->sync($request->cargos);
        }

        return redirect()->route('admin.users')->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Exibe a página do Query Builder para administradores
     */
    public function showQueryBuilder(Request $request): View
    {
        try {
            // Obter lista de tabelas do banco de dados
            $tableNames = $this->getSystemTables();
            
            // Obter consultas favoritas do usuário (se implementado)
            $favorites = collect(); // Por enquanto vazio, pode ser implementado futuramente
            
            return view('admin.query-builder', compact('tableNames', 'favorites'));
        } catch (\Exception $e) {
            Log::error('Erro ao carregar Query Builder: ' . $e->getMessage());
            return redirect()->route('admin.dashboard')->with('error', 'Erro ao carregar o Query Builder.');
        }
    }


}