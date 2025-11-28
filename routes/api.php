<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Finance\FinanceSettingsController;
use App\Http\Controllers\Finance\FinanceGatewaysController;
use App\Http\Controllers\Finance\WebhookController;
use App\Http\Controllers\Finance\PlansController;
use App\Http\Controllers\Finance\SubscriptionsController;
use App\Http\Controllers\Finance\InvoicesController;
use App\Http\Controllers\Finance\PaymentsController;
use App\Http\Controllers\Finance\ChargeMethodsController;
use App\Http\Controllers\PlanejamentoController;
use App\Http\Controllers\CorporativoController;
use App\Http\Controllers\Api\EscolaController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rotas existentes do planejamento
Route::get('/tipos-professor', [App\Http\Controllers\PlanejamentoController::class, 'getTiposProfessor']);
Route::get('/turnos-disponiveis', [App\Http\Controllers\PlanejamentoController::class, 'getTurnosDisponiveis']);
Route::get('/niveis-ensino', [App\Http\Controllers\PlanejamentoController::class, 'getNiveisEnsino']);
Route::get('/grupos-educacionais', [App\Http\Controllers\PlanejamentoController::class, 'getGruposEducacionais']);
Route::get('/planejamentos/disciplinas-por-modalidade-turno-grupo', [App\Http\Controllers\PlanejamentoController::class, 'getDisciplinasPorModalidadeTurnoGrupo']);

// Rotas para escalas (protegidas por autenticação web)
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/funcionarios/{funcionario}/escalas', [App\Http\Controllers\FuncionarioTemplateController::class, 'getEscalasFuncionario']);
    
    // Rotas para turnos
    Route::get('/turnos/{id}', [App\Http\Controllers\TurnoController::class, 'showApi']);
    
    // Rotas para conversas e chat
    Route::get('/usuarios/buscar', [App\Http\Controllers\ConversaController::class, 'buscarUsuarios']);
    Route::post('/conversas/{conversa}/participantes', [App\Http\Controllers\ConversaController::class, 'adicionarParticipante']);
    Route::delete('/conversas/{conversa}/participantes/{usuario}', [App\Http\Controllers\ConversaController::class, 'removerParticipante']);
    Route::get('/conversas/{conversa}/participantes', [App\Http\Controllers\ConversaController::class, 'listarParticipantes']);
    Route::post('/conversas/{conversa}/digitando', [App\Http\Controllers\ConversaController::class, 'indicarDigitacao']);
    Route::get('/conversas/{conversa}/status', [App\Http\Controllers\ConversaController::class, 'obterStatus']);
});

// Rota pública para módulos disponíveis
Route::get('/escolas/available-modules', [EscolaController::class, 'getAvailableModules']); // Listar módulos disponíveis

// Rota para listar módulos (protegida por autenticação)
Route::middleware(['web', 'auth'])->get('/modules', [App\Http\Controllers\ModuleController::class, 'index']);

// Rotas da API para Escolas (protegidas por middleware admin)
Route::middleware(['web', 'admin.auth'])->prefix('escolas')->group(function () {
    // CRUD básico
    Route::get('/', [EscolaController::class, 'index']); // Listar escolas
    Route::post('/', [EscolaController::class, 'store']); // Criar escola
    Route::get('/{id}', [EscolaController::class, 'show']); // Mostrar escola
    Route::put('/{id}', [EscolaController::class, 'update']); // Atualizar escola
    Route::delete('/{id}', [EscolaController::class, 'destroy']); // Excluir escola
    
    // Ações específicas
    Route::post('/{id}/toggle-status', [EscolaController::class, 'toggleStatus']); // Ativar/Desativar
    Route::post('/{id}/inactivate', [EscolaController::class, 'inactivate']); // Inativar escola
    Route::post('/{id}/clear-cache', [EscolaController::class, 'clearCache']); // Limpar cache
    
    // Gerenciamento de módulos
    Route::get('/{id}/modules', [EscolaController::class, 'getModules']); // Listar módulos da escola
    Route::post('/{id}/modules', [EscolaController::class, 'updateModules']); // Atualizar módulos da escola
    
    // Estatísticas
    Route::get('/{id}/stats', [EscolaController::class, 'stats']); // Estatísticas da escola
    Route::get('/stats/global', [EscolaController::class, 'globalStats']); // Estatísticas globais
});

// Rotas da API para dados do painel administrativo
Route::middleware(['web', 'admin.auth'])->prefix('admin')->group(function () {
    // Dashboard data
    Route::get('/dashboard-data', function () {
        $stats = [
            'total_escolas' => \App\Models\Escola::count(),
            'escolas_ativas' => \App\Models\Escola::where('ativo', true)->count(),
            'total_usuarios' => \App\Models\User::count(),
            'receita_mensal' => \App\Models\Escola::where('ativo', true)->sum('valor_mensalidade'),
            'escolas_inadimplentes' => \App\Models\Escola::where('em_dia', false)->count(),
        ];
        
        return response()->json($stats);
    });
    
    // Dados para gráficos
    Route::get('/chart-data', function () {
        $crescimento = \App\Models\Escola::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as mes, COUNT(*) as count')
            ->groupBy('mes')
            ->orderBy('mes')
            ->limit(12)
            ->get();
            
        $receita = \App\Models\Escola::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as mes, SUM(valor_mensalidade) as valor')
            ->where('ativo', true)
            ->groupBy('mes')
            ->orderBy('mes')
            ->limit(12)
            ->get();
            
        return response()->json([
            'crescimento' => $crescimento,
            'receita' => $receita
        ]);
    });
    
    // Buscar escolas com filtros
    Route::get('/escolas/search', function (Request $request) {
        $query = \App\Models\Escola::query();
        
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->search . '%')
                  ->orWhere('cnpj', 'like', '%' . $request->search . '%')
                  ->orWhere('razao_social', 'like', '%' . $request->search . '%');
            });
        }
        
        if ($request->has('status') && $request->status !== '') {
            $query->where('ativo', $request->status === 'ativo');
        }
        
        if ($request->has('plano') && $request->plano) {
            $query->where('plano', $request->plano);
        }
        
        if ($request->has('pagamento') && $request->pagamento !== '') {
            $query->where('em_dia', $request->pagamento === 'em_dia');
        }
        
        $escolas = $query->withCount('users')
            ->orderBy($request->get('sort', 'created_at'), $request->get('order', 'desc'))
            ->paginate($request->get('per_page', 15));
            
        return response()->json($escolas);
    });
    
    // Exportar dados
    Route::get('/export/escolas', function (Request $request) {
        $format = $request->get('format', 'excel'); // excel, csv, pdf
        
        $escolas = \App\Models\Escola::withCount('users')->get();
        
        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="escolas.csv"',
            ];
            
            $callback = function() use ($escolas) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Nome', 'CNPJ', 'Razão Social', 'Email', 'Plano', 'Usuários', 'Status', 'Pagamento']);
                
                foreach ($escolas as $escola) {
                    fputcsv($file, [
                        $escola->nome,
                        $escola->cnpj,
                        $escola->razao_social,
                        $escola->email,
                        $escola->plano,
                        $escola->users_count,
                        $escola->ativo ? 'Ativa' : 'Inativa',
                        $escola->em_dia ? 'Em dia' : 'Inadimplente'
                    ]);
                }
                
                fclose($file);
            };
            
            return response()->stream($callback, 200, $headers);
        }
        
        return response()->json(['message' => 'Formato não suportado'], 400);
    });
    
    // Validar CNPJ
    Route::post('/validate-cnpj', function (Request $request) {
        $cnpj = preg_replace('/[^0-9]/', '', $request->cnpj);
        
        // Validação básica de CNPJ
        if (strlen($cnpj) !== 14) {
            return response()->json(['valid' => false, 'message' => 'CNPJ deve ter 14 dígitos']);
        }
        
        // Verificar se já existe
        $exists = \App\Models\Escola::where('cnpj', $cnpj)
            ->when($request->escola_id, function($q) use ($request) {
                $q->where('id', '!=', $request->escola_id);
            })
            ->exists();
            
        if ($exists) {
            return response()->json(['valid' => false, 'message' => 'CNPJ já cadastrado']);
        }
        
        return response()->json(['valid' => true]);
    });
    
    // Buscar CEP
    Route::get('/cep/{cep}', function ($cep) {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        
        if (strlen($cep) !== 8) {
            return response()->json(['error' => 'CEP inválido'], 400);
        }
        
        // Simular busca de CEP (em produção, usar API real como ViaCEP)
        $dados = [
            'cep' => $cep,
            'logradouro' => 'Rua Exemplo',
            'bairro' => 'Centro',
            'localidade' => 'Cidade Exemplo',
            'uf' => 'SP'
        ];
        
        return response()->json($dados);
    });
});

// API de permissões (usada na tela corporativa de permissões)
Route::middleware(['web', 'admin.auth'])->prefix('permissions')->group(function () {
    // Cargos por permissão
    Route::get('/{id}/roles', [CorporativoController::class, 'getPermissionRoles']);

    // Exportar lista de permissões (CSV simples)
    Route::get('/export', function () {
        $permissoes = \App\Models\Permissao::withCount('cargos')->orderBy('modulo')->orderBy('nome')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="permissoes.csv"',
        ];

        $callback = function() use ($permissoes) {
            $output = fopen('php://output', 'w');
            // BOM para Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, ['ID', 'Nome', 'Módulo', 'Descrição', 'Cargos vinculados']);
            foreach ($permissoes as $p) {
                fputcsv($output, [
                    $p->id,
                    $p->nome,
                    $p->modulo,
                    $p->descricao,
                    $p->cargos_count,
                ]);
            }
            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    });
});

// API Financeira (v1) — settings & gateways (session-based auth)
Route::middleware(['web', 'auth'])->prefix('v1/finance')->group(function () {
    // Configurações financeiras por escola
    Route::get('/settings', [FinanceSettingsController::class, 'show']);
    Route::put('/settings', [FinanceSettingsController::class, 'update']);

    // Gateways de pagamento por escola
    Route::get('/gateways', [FinanceGatewaysController::class, 'index']);
    Route::get('/gateways/{id}', [FinanceGatewaysController::class, 'show']);
    Route::post('/gateways', [FinanceGatewaysController::class, 'store']);
    Route::put('/gateways/{id}', [FinanceGatewaysController::class, 'update']);
    Route::delete('/gateways/{id}', [FinanceGatewaysController::class, 'destroy']);

    // Planos de cobrança
    Route::get('/plans', [PlansController::class, 'index']);
    Route::get('/plans/{id}', [PlansController::class, 'show']);
    Route::post('/plans', [PlansController::class, 'store']);
    Route::put('/plans/{id}', [PlansController::class, 'update']);
    Route::delete('/plans/{id}', [PlansController::class, 'destroy']);

    // Formas de cobrança (gateway + método)
    Route::get('/charge-methods', [ChargeMethodsController::class, 'index']);
    Route::get('/charge-methods/{id}', [ChargeMethodsController::class, 'show']);
    Route::post('/charge-methods', [ChargeMethodsController::class, 'store']);
    Route::put('/charge-methods/{id}', [ChargeMethodsController::class, 'update']);
    Route::delete('/charge-methods/{id}', [ChargeMethodsController::class, 'destroy']);

    // Assinaturas
    Route::get('/subscriptions', [SubscriptionsController::class, 'index']);
    Route::get('/subscriptions/{id}', [SubscriptionsController::class, 'show']);
    Route::post('/subscriptions', [SubscriptionsController::class, 'store']);
    Route::put('/subscriptions/{id}', [SubscriptionsController::class, 'update']);
    Route::delete('/subscriptions/{id}', [SubscriptionsController::class, 'destroy']);

    // Faturas
    Route::get('/invoices', [InvoicesController::class, 'index']);
    Route::get('/invoices/{id}', [InvoicesController::class, 'show']);
    Route::post('/invoices', [InvoicesController::class, 'store']);
    Route::put('/invoices/{id}', [InvoicesController::class, 'update']);
    Route::delete('/invoices/{id}', [InvoicesController::class, 'destroy']);
    // Sincronizar status diretamente no gateway
    Route::get('/invoices/{id}/sync-gateway', [InvoicesController::class, 'syncGatewayStatus']);
    // Cancelar fatura (única e em lote)
    Route::post('/invoices/{id}/cancel', [InvoicesController::class, 'cancel']);
    Route::post('/invoices/cancel-batch', [InvoicesController::class, 'cancelBatch']);
    // Reenviar cobrança por e-mail ao pagador
    Route::post('/invoices/{id}/resend-email', [InvoicesController::class, 'resendEmail']);

    // Pagamentos
    Route::get('/payments', [PaymentsController::class, 'index']);
    Route::get('/payments/{id}', [PaymentsController::class, 'show']);
    Route::post('/payments', [PaymentsController::class, 'store']);
    Route::put('/payments/{id}', [PaymentsController::class, 'update']);
    Route::delete('/payments/{id}', [PaymentsController::class, 'destroy']);
});

// Webhooks financeiros — público para provedores (validação no processamento)
Route::prefix('v1')->group(function () {
    Route::post('/webhooks/gateway/{alias}', [WebhookController::class, 'receive']);
});

// Rotas públicas da API (sem autenticação)
Route::prefix('public')->group(function () {
    // Estatísticas públicas
    Route::get('/stats', function () {
        return response()->json([
            'total_escolas' => \App\Models\Escola::where('ativo', true)->count(),
            'total_usuarios' => \App\Models\User::count(),
            'sistema_online' => true,
            'versao' => '1.0.0'
        ]);
    });
});