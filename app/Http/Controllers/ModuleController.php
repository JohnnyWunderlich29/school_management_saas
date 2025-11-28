<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\EscolaModule;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ModuleController extends Controller
{
    /**
     * Exibe a página de módulos
     */
    public function index(Request $request)
    {
        // Se for uma requisição AJAX/API, retorna JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->getModulesData($request);
        }
        
        // Caso contrário, retorna a view
        return view('modules.index');
    }
    
    /**
     * Lista todos os módulos disponíveis para a escola atual (API)
     */
    public function getModulesData(Request $request): JsonResponse
    {
        try {
            // Determinar escola alvo
            $escola = null;
            $schoolId = $request->input('school_id');

            if ($schoolId) {
                // Apenas super admins podem consultar módulos de outra escola via parâmetro
                if (!$request->user()->isSuperAdmin() && $request->user()->escola_id != (int)$schoolId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Você não pode gerenciar módulos de outra escola'
                    ], 403);
                }
                $escola = \App\Models\Escola::find($schoolId);
            } else {
                // Usuário normal: usar escola vinculada
                $escola = $request->user()->escola;
                // Super admin sem escola vinculada: usar escola selecionada na sessão
                if (!$escola && $request->user()->isSuperAdmin() && session('escola_atual')) {
                    $escola = \App\Models\Escola::find(session('escola_atual'));
                }
            }
            
            if (!$escola) {
                return response()->json([
                    'success' => false,
                    'message' => 'Escola não encontrada'
                ], 404);
            }

            // Busca todos os módulos ativos
            $modules = Module::active()->ordered()->get();
            
            // Busca os módulos já contratados pela escola
            $contractedModules = $escola->escolaModules()
                ->with('module')
                ->get()
                ->keyBy('module_id');

            // Prepara os dados dos módulos
            $moduleData = $modules->map(function ($module) use ($contractedModules) {
                $contracted = $contractedModules->get($module->id);
                
                return [
                    'id' => $module->id,
                    'name' => $module->name,
                    'display_name' => $module->display_name,
                    'description' => $module->description,
                    'icon' => $module->icon,
                    'color' => $module->color,
                    'price' => $module->price,
                    'formatted_price' => $module->formatted_price,
                    'features' => $module->features,
                    'category' => $module->category,
                    'category_display' => $module->category_display,
                    'is_core' => $module->is_core,
                    'is_contracted' => $contracted !== null,
                    'is_active' => $contracted ? $contracted->is_active : false,
                    'monthly_price' => $contracted ? $contracted->monthly_price : $module->price,
                    'contracted_at' => $contracted ? $contracted->contracted_at : null,
                    'expires_at' => $contracted ? $contracted->expires_at : null,
                    'status' => $contracted ? $contracted->status : 'not_contracted',
                    'status_description' => $contracted ? $contracted->status_description : 'Não contratado',
                    'status_color' => $contracted ? $contracted->status_color : 'gray',
                ];
            });

            // Agrupa por categoria
            $groupedModules = $moduleData->groupBy('category');
            $categories = Module::getCategories();

            return response()->json([
                'success' => true,
                'data' => [
                    'modules' => $moduleData,
                    'grouped_modules' => $groupedModules,
                    'categories' => $categories,
                    'total_modules_price' => $escola->getTotalModulesPrice(),
                    'total_monthly_value' => $escola->getTotalMonthlyValue(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar módulos: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Contrata um módulo para a escola
     */
    public function contract(Request $request, Module $module): JsonResponse
    {
        try {
            // Determinar escola alvo
            $escola = null;
            $schoolId = $request->input('school_id');

            if ($schoolId) {
                if (!$request->user()->isSuperAdmin() && $request->user()->escola_id != (int)$schoolId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Você não pode gerenciar módulos de outra escola'
                    ], 403);
                }
                $escola = \App\Models\Escola::find($schoolId);
            } else {
                $escola = $request->user()->escola;
                if (!$escola && $request->user()->isSuperAdmin() && session('escola_atual')) {
                    $escola = \App\Models\Escola::find(session('escola_atual'));
                }
            }
            
            if (!$escola) {
                return response()->json([
                    'success' => false,
                    'message' => 'Escola não encontrada'
                ], 404);
            }

            // Verifica se o módulo já está contratado e ativo
            $existingContract = $escola->escolaModules()
                ->where('module_id', $module->id)
                ->where('is_active', true)
                ->first();

            if ($existingContract) {
                return response()->json([
                    'success' => false,
                    'message' => 'Módulo já está contratado e ativo'
                ], 400);
            }

            DB::beginTransaction();

            // Contrata o módulo
            $escolaModule = $escola->contractModule($module, null, $request->user()->id);

            // Atualizar valor da mensalidade da escola
            $totalModulesPrice = $escola->getTotalModulesPrice();
            $basePrice = $escola->getPlanoPreco();
            $escola->update([
                'valor_mensalidade' => $basePrice + $totalModulesPrice
            ]);
            
            \Log::info('Valor mensalidade atualizado', [
                'escola_id' => $escola->id,
                'base_price' => $basePrice,
                'modules_price' => $totalModulesPrice,
                'total' => $escola->valor_mensalidade
            ]);

            DB::commit();

            // Invalidar cache de módulos disponíveis da escola
            cache()->forget("school_modules_{$escola->id}");

            return response()->json([
                'success' => true,
                'message' => 'Módulo contratado com sucesso',
                'data' => [
                    'module' => $module,
                    'contract' => $escolaModule,
                    'new_monthly_value' => $escola->getTotalMonthlyValue()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao contratar módulo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao contratar módulo'
            ], 500);
        }
    }

    /**
     * Cancela um módulo da escola
     */
    public function cancel(Request $request, Module $module): JsonResponse
    {
        try {
            // Determinar escola alvo
            $escola = null;
            $schoolId = $request->input('school_id');

            if ($schoolId) {
                if (!$request->user()->isSuperAdmin() && $request->user()->escola_id != (int)$schoolId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Você não pode gerenciar módulos de outra escola'
                    ], 403);
                }
                $escola = \App\Models\Escola::find($schoolId);
            } else {
                $escola = $request->user()->escola;
                if (!$escola && $request->user()->isSuperAdmin() && session('escola_atual')) {
                    $escola = \App\Models\Escola::find(session('escola_atual'));
                }
            }
            
            if (!$escola) {
                return response()->json([
                    'success' => false,
                    'message' => 'Escola não encontrada'
                ], 404);
            }

            // Verifica se é um módulo core
            if ($module->is_core) {
                return response()->json([
                    'success' => false,
                    'message' => 'Módulos essenciais não podem ser cancelados'
                ], 400);
            }

            DB::beginTransaction();

            // Cancela o módulo
            $cancelled = $escola->cancelModule($module);

            if (!$cancelled) {
                return response()->json([
                    'success' => false,
                    'message' => 'Módulo não encontrado ou já cancelado'
                ], 404);
            }

            // Atualizar valor da mensalidade da escola
            $totalModulesPrice = $escola->getTotalModulesPrice();
            $basePrice = $escola->getPlanoPreco();
            $escola->update([
                'valor_mensalidade' => $basePrice + $totalModulesPrice
            ]);
            
            \Log::info('Valor mensalidade atualizado após cancelamento', [
                'escola_id' => $escola->id,
                'base_price' => $basePrice,
                'modules_price' => $totalModulesPrice,
                'total' => $escola->valor_mensalidade
            ]);

            DB::commit();

            // Invalidar cache de módulos disponíveis da escola
            cache()->forget("school_modules_{$escola->id}");

            return response()->json([
                'success' => true,
                'message' => 'Módulo cancelado com sucesso',
                'data' => [
                    'new_monthly_value' => $escola->getTotalMonthlyValue()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao cancelar módulo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar módulo'
            ], 500);
        }
    }

    /**
     * Ativa/desativa um módulo já contratado
     */
    public function toggle(Request $request, Module $module): JsonResponse
    {
        try {
            $escola = $request->user()->escola;
            
            if (!$escola) {
                return response()->json([
                    'success' => false,
                    'message' => 'Escola não encontrada'
                ], 404);
            }

            $escolaModule = $escola->escolaModules()
                ->where('module_id', $module->id)
                ->first();

            if (!$escolaModule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Módulo não contratado'
                ], 404);
            }

            // Verifica se é um módulo core
            if ($module->is_core && $escolaModule->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Módulos essenciais não podem ser desativados'
                ], 400);
            }

            DB::beginTransaction();

            // Alterna o status
            $escolaModule->update([
                'is_active' => !$escolaModule->is_active
            ]);

            // Atualizar valor da mensalidade da escola
            $totalModulesPrice = $escola->getTotalModulesPrice();
            $basePrice = $escola->getPlanoPreco();
            $escola->update([
                'valor_mensalidade' => $basePrice + $totalModulesPrice
            ]);
            
            \Log::info('Valor mensalidade atualizado após toggle', [
                'escola_id' => $escola->id,
                'base_price' => $basePrice,
                'modules_price' => $totalModulesPrice,
                'total' => $escola->valor_mensalidade
            ]);

            DB::commit();

            $action = $escolaModule->is_active ? 'ativado' : 'desativado';

            // Invalidar cache de módulos disponíveis da escola
            cache()->forget("school_modules_{$escola->id}");

            return response()->json([
                'success' => true,
                'message' => "Módulo {$action} com sucesso",
                'data' => [
                    'is_active' => $escolaModule->is_active,
                    'new_monthly_value' => $escola->getTotalMonthlyValue()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao alternar módulo: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao alternar status do módulo'
            ], 500);
        }
    }
}