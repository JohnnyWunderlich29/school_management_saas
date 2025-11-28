<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Escola;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class EscolaController extends Controller
{
    /**
     * Listar todas as escolas
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Escola::query();

            // Filtros
            if ($request->has('ativo')) {
                $query->where('ativo', $request->boolean('ativo'));
            }

            if ($request->has('plano')) {
                $query->where('plano', $request->plano);
            }

            if ($request->has('em_dia')) {
                $query->where('em_dia', $request->boolean('em_dia'));
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nome', 'like', "%{$search}%")
                      ->orWhere('razao_social', 'like', "%{$search}%")
                      ->orWhere('cnpj', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Ordenação
            $sortBy = $request->get('sort_by', 'nome');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginação
            $perPage = $request->get('per_page', 15);
            $escolas = $query->withCount(['users', 'funcionarios'])->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $escolas,
                'message' => 'Escolas listadas com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao listar escolas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibir uma escola específica
     */
    public function show($id): JsonResponse
    {
        try {
            $escola = Escola::with(['users', 'funcionarios', 'salas'])
                           ->withCount(['users', 'funcionarios'])
                           ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $escola,
                'message' => 'Escola encontrada com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Escola não encontrada'
            ], 404);
        }
    }

    /**
     * Criar uma nova escola
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nome' => 'required|string|max:255',
                'cnpj' => 'required|string|size:18|unique:escolas,cnpj',
                'razao_social' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'telefone' => 'nullable|string|max:20',
                'celular' => 'nullable|string|max:20',
                'cep' => 'nullable|string|size:9',
                'endereco' => 'nullable|string|max:255',
                'numero' => 'nullable|string|max:10',
                'complemento' => 'nullable|string|max:100',
                'bairro' => 'nullable|string|max:100',
                'cidade' => 'nullable|string|max:100',
                'estado' => 'nullable|string|size:2',
                'plano' => 'required|in:basico,premium,enterprise',
                'max_usuarios' => 'nullable|integer|min:1',
                'max_alunos' => 'nullable|integer|min:1',
                'valor_mensalidade' => 'nullable|numeric|min:0',
                'data_vencimento' => 'nullable|date',
                'descricao' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $escola = Escola::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $escola,
                'message' => 'Escola criada com sucesso'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar escola: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar uma escola
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $escola = Escola::findOrFail($id);
            
            // Verificar permissões do usuário
            $user = auth()->user();
            $isSuperAdmin = $user && $user->isSuperAdmin();
            
            // Campos que só podem ser editados por super administradores
            $superAdminFields = ['nome', 'cnpj', 'razao_social', 'valor_mensalidade', 'data_vencimento', 'plano', 'max_usuarios', 'max_alunos'];
            
            // Campos que podem ser editados por suporte e super admin
            $suporteFields = ['email', 'telefone', 'celular', 'cep', 'endereco', 'numero', 'complemento', 'bairro', 'cidade', 'estado', 'configuracoes', 'logo', 'descricao', 'ativo'];
            
            // Filtrar dados baseado nas permissões
            $allowedData = [];
            
            foreach ($request->all() as $key => $value) {
                if ($isSuperAdmin) {
                    // Super admin pode editar todos os campos
                    $allowedData[$key] = $value;
                } else {
                    // Usuários de suporte só podem editar campos específicos
                    if (in_array($key, $suporteFields)) {
                        $allowedData[$key] = $value;
                    }
                }
            }
            
            // Se não for super admin e tentar editar campos restritos, retornar erro
            if (!$isSuperAdmin) {
                $restrictedFields = array_intersect(array_keys($request->all()), $superAdminFields);
                if (!empty($restrictedFields)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Você não tem permissão para editar os campos: ' . implode(', ', $restrictedFields)
                    ], 403);
                }
            }

            // Validação baseada nos campos permitidos
            $validationRules = [];
            
            if (isset($allowedData['nome'])) {
                $validationRules['nome'] = 'required|string|max:255';
            }
            if (isset($allowedData['cnpj'])) {
                $validationRules['cnpj'] = 'required|string|size:18|unique:escolas,cnpj,' . $id;
            }
            if (isset($allowedData['razao_social'])) {
                $validationRules['razao_social'] = 'required|string|max:255';
            }
            if (isset($allowedData['email'])) {
                $validationRules['email'] = 'nullable|email|max:255';
            }
            if (isset($allowedData['telefone'])) {
                $validationRules['telefone'] = 'nullable|string|max:20';
            }
            if (isset($allowedData['celular'])) {
                $validationRules['celular'] = 'nullable|string|max:20';
            }
            if (isset($allowedData['cep'])) {
                $validationRules['cep'] = 'nullable|string|size:9';
            }
            if (isset($allowedData['endereco'])) {
                $validationRules['endereco'] = 'nullable|string|max:255';
            }
            if (isset($allowedData['numero'])) {
                $validationRules['numero'] = 'nullable|string|max:10';
            }
            if (isset($allowedData['complemento'])) {
                $validationRules['complemento'] = 'nullable|string|max:100';
            }
            if (isset($allowedData['bairro'])) {
                $validationRules['bairro'] = 'nullable|string|max:100';
            }
            if (isset($allowedData['cidade'])) {
                $validationRules['cidade'] = 'nullable|string|max:100';
            }
            if (isset($allowedData['estado'])) {
                $validationRules['estado'] = 'nullable|string|size:2';
            }
            if (isset($allowedData['plano'])) {
                // Aceitar qualquer slug existente na tabela de planos
                $validationRules['plano'] = 'required|string|exists:plans,slug';
            }
            if (isset($allowedData['valor_mensalidade'])) {
                $validationRules['valor_mensalidade'] = 'nullable|numeric|min:0';
            }
            if (isset($allowedData['data_vencimento'])) {
                $validationRules['data_vencimento'] = 'nullable|date';
            }
            if (isset($allowedData['ativo'])) {
                $validationRules['ativo'] = 'boolean';
            }
            if (isset($allowedData['descricao'])) {
                $validationRules['descricao'] = 'nullable|string';
            }
            if (isset($allowedData['logo'])) {
                $validationRules['logo'] = 'nullable|url|max:255';
            }
            if (isset($allowedData['max_usuarios'])) {
                $validationRules['max_usuarios'] = 'nullable|integer|min:1|max:10000';
            }
            if (isset($allowedData['max_alunos'])) {
                $validationRules['max_alunos'] = 'nullable|integer|min:1|max:50000';
            }

            $validator = Validator::make($allowedData, $validationRules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Se um plano válido foi informado, vincular também o plan_id
            if (isset($allowedData['plano'])) {
                $plan = \App\Models\Plan::where('slug', $allowedData['plano'])->first();
                if ($plan) {
                    $allowedData['plan_id'] = $plan->id;
                }
            }

            $escola->update($allowedData);

            return response()->json([
                'success' => true,
                'data' => $escola->fresh(),
                'message' => 'Escola atualizada com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar escola: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Excluir uma escola
     */
    public function destroy($id): JsonResponse
    {
        try {
            $escola = Escola::findOrFail($id);
            
            // Verificar se a escola tem usuários associados
            if ($escola->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir uma escola que possui usuários associados'
                ], 422);
            }

            $escola->delete();

            return response()->json([
                'success' => true,
                'message' => 'Escola excluída com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir escola: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter estatísticas das escolas
     */
    public function stats(): JsonResponse
    {
        try {
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
                ],
                'total_usuarios' => DB::table('users')->whereNotNull('escola_id')->count(),
                'total_funcionarios' => DB::table('funcionarios')->whereNotNull('escola_id')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Estatísticas obtidas com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao obter estatísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ativar/Desativar escola
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $escola = Escola::findOrFail($id);
            $escola->ativo = !$escola->ativo;
            $escola->save();

            // Se a escola foi inativada, desativar todos os módulos
            if (!$escola->ativo) {
                \App\Models\SchoolLicense::where('school_id', $id)
                    ->update(['is_active' => false]);
            }

            $status = $escola->ativo ? 'ativada' : 'desativada';
            $moduleMessage = !$escola->ativo ? ' e todos os módulos foram desativados' : '';

            return response()->json([
                'success' => true,
                'data' => $escola,
                'message' => "Escola {$status} com sucesso{$moduleMessage}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alterar status da escola: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obter módulos licenciados de uma escola
     */
    public function getModules($id): JsonResponse
    {
        try {
            $escola = Escola::findOrFail($id);
            
        
            // Buscar módulos ativos da escola
            $modules = \App\Models\EscolaModule::where('escola_id', $id)
                ->where('is_active', true)
                ->where(function($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                })
                ->with('module')
                ->get()
                ->map(function($escolaModule) {
                    return [
                        'id' => $escolaModule->module_id,
                        'name' => $escolaModule->module->name,
                        'display_name' => $escolaModule->module->display_name,
                        'description' => $escolaModule->module->description,
                        'icon' => $escolaModule->module->icon,
                        'color' => $escolaModule->module->color,
                        'is_active' => $escolaModule->is_active,
                        'expires_at' => $escolaModule->expires_at
                    ];
                });

            return response()->json([
                'success' => true,
                'modules' => $modules
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar módulos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Atualizar módulos licenciados de uma escola
     */
    public function updateModules(Request $request, $id): JsonResponse
    {
        try {
            $escola = Escola::findOrFail($id);
            
            $modules = $request->input('modules', []);
            
            // Verificar permissões
            $user = auth()->user();
            if (!$user->isSuperAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Apenas super administradores podem gerenciar módulos'
                ], 403);
            }

            // Desativar todos os módulos atuais da escola
            \App\Models\EscolaModule::where('escola_id', $id)
                ->update(['is_active' => false]);

            // Ativar módulos selecionados
            foreach ($modules as $moduleId) {
                \App\Models\EscolaModule::updateOrCreate(
                    [
                        'escola_id' => $id,
                        'module_id' => $moduleId
                    ],
                    [
                        'is_active' => true,
                        'contracted_at' => now(),
                        'expires_at' => now()->addYears(10), // Licença de 10 anos
                        'monthly_price' => 0.00,
                        'contracted_by' => auth()->id(),
                        'notes' => 'Módulo ativado via painel administrativo'
                    ]
                );
            }

            // Invalidar cache de módulos disponíveis da escola
            cache()->forget("school_modules_{$id}");

            return response()->json([
                'success' => true,
                'message' => 'Módulos atualizados com sucesso',
                'modules' => $modules
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar módulos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Inativar uma escola
     */
    public function inactivate($id): JsonResponse
    {
        try {
            $escola = Escola::findOrFail($id);
            
            $escola->update(['ativo' => false]);

            return response()->json([
                'success' => true,
                'data' => $escola->fresh(),
                'message' => 'Escola inativada com sucesso'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao inativar escola: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retorna lista dinâmica de módulos disponíveis
     */
    public function getAvailableModules(): JsonResponse
    {
        try {
            // Buscar módulos da tabela modules
            $modules = \App\Models\Module::where('is_active', true)->get();
            
            $availableModules = $modules->map(function($module) {
                return [
                    'id' => $module->id,
                    'name' => $module->name,
                    'display_name' => $module->display_name,
                    'description' => $module->description,
                    'icon' => $module->icon,
                    'color' => $module->color,
                    'price' => $module->price,
                    'is_active' => $module->is_active,
                    'category' => $module->category,
                    'features' => $module->features
                ];
            });
            
            return response()->json([
                'success' => true,
                'modules' => $availableModules
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar módulos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Converte chave do módulo para nome de exibição
     */
    private function getModuleDisplayName(string $moduleKey): string
    {
        $displayNames = [
            'comunicacao_module' => 'Comunicação',
            'alunos_module' => 'Alunos',
            'funcionarios_module' => 'Funcionários',
            'academico_module' => 'Acadêmico',
            'administracao_module' => 'Administração',
            'relatorios_module' => 'Relatórios',
            'financeiro_module' => 'Financeiro',
            'biblioteca_module' => 'Biblioteca',
            'transporte_module' => 'Transporte',
        ];
        
        return $displayNames[$moduleKey] ?? ucfirst(str_replace('_module', '', $moduleKey));
    }
}