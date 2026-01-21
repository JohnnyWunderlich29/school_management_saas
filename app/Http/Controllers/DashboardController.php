<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Aluno;
use App\Models\Funcionario;
use App\Models\Responsavel;
use App\Models\Presenca;
use App\Models\Escala;
use App\Models\Sala;
use App\Models\Despesa;
use App\Models\Finance\Payment;
use App\Models\Finance\Invoice;
use App\Models\Finance\Subscription;
use App\Models\Conversa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Exibe o dashboard com informações gerais do sistema
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Debug: Log da escola atual ao carregar dashboard
        \Log::info('DEBUG DASHBOARD - Carregando dashboard', [
            'user_id' => auth()->user()->id,
            'user_email' => auth()->user()->email,
            'user_escola_id' => auth()->user()->escola_id,
            'session_escola_atual' => session('escola_atual'),
            'is_super_admin' => auth()->user()->isSuperAdmin(),
            'url' => request()->url()
        ]);

        // Período Analytics: persistência em sessão, limite 6 meses, default 30 dias
        $clearPeriodo = $request->boolean('clear_periodo');
        if ($clearPeriodo) {
            Session::forget(['dashboard_analytics_inicio', 'dashboard_analytics_fim']);
        }

        $inicioInput = $request->input('inicio', Session::get('dashboard_analytics_inicio'));
        $fimInput = $request->input('fim', Session::get('dashboard_analytics_fim'));

        if ($inicioInput && !$fimInput) {
            $fimInput = $inicioInput;
        }
        if ($fimInput && !$inicioInput) {
            $inicioInput = $fimInput;
        }

        $clamped = false;
        // Se limpar, restaurar mês atual como padrão
        if ($clearPeriodo) {
            $inicio = Carbon::now()->startOfMonth()->startOfDay();
            $fim = Carbon::now()->endOfMonth()->endOfDay();
        } else if ($inicioInput && $fimInput) {
            try {
                $inicio = Carbon::parse($inicioInput)->startOfDay();
                $fim = Carbon::parse($fimInput)->endOfDay();
            } catch (\Exception $e) {
                $fim = Carbon::today()->endOfDay();
                $inicio = $fim->copy()->subDays(29)->startOfDay();
            }
            if ($inicio->gt($fim)) {
                [$inicio, $fim] = [$fim, $inicio];
            }

            // Limitar fim a no máximo 6 meses após início
            $limitEnd = $inicio->copy()->addMonths(6)->endOfDay();
            if ($fim->gt($limitEnd)) {
                $fim = $limitEnd;
                $clamped = true;
            }
        } else {
            // Default inicial continua 30 dias
            $fim = Carbon::today()->endOfDay();
            $inicio = $fim->copy()->subDays(29)->startOfDay();
        }

        // Persistir em sessão
        Session::put('dashboard_analytics_inicio', $inicio->toDateString());
        Session::put('dashboard_analytics_fim', $fim->toDateString());

        $hoje = Carbon::today();

        // Determinar escola_id baseado no tipo de usuário
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
        } else {
            $escolaId = auth()->user()->escola_id;
        }

        \Log::info('DEBUG DASHBOARD - Escola ID para filtros', [
            'escola_id_filtro' => $escolaId,
            'is_super_admin' => auth()->user()->isSuperAdmin(),
            'session_escola_atual' => session('escola_atual')
        ]);
        $estatisticas = collect([
            'totalAlunos' => Aluno::ativos()->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))->count(),
            'totalResponsaveis' => Responsavel::when($escolaId, fn($q) => $q->where('escola_id', $escolaId))->count(),
            'totalFuncionarios' => Funcionario::ativos()->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))->count(),
            'totalSalas' => Sala::ativas()->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))->count(),
        ]);

        // Estatísticas de presenças do dia usando scopes
        $presencasEstatisticas = Presenca::hoje()
            ->when($escolaId, function ($q) use ($escolaId) {
                $q->whereHas('aluno', fn($query) => $query->where('escola_id', $escolaId));
            })
            ->selectRaw('
                COUNT(*) as total_presencas,
                SUM(CASE WHEN presente = true THEN 1 ELSE 0 END) as presentes,
                SUM(CASE WHEN presente = false THEN 1 ELSE 0 END) as ausentes
            ')
            ->first();

        $presencasHoje = $presencasEstatisticas->total_presencas ?? 0;
        $faltasHoje = $presencasEstatisticas->ausentes ?? 0;

        // Escalas do dia
        $escalasHoje = Escala::where('data', $hoje)
            ->when($escolaId, function ($q) use ($escolaId) {
                $q->whereHas('funcionario', fn($query) => $query->where('escola_id', $escolaId));
            })
            ->count();

        // Últimos 5 alunos cadastrados usando scope
        $ultimosAlunos = Aluno::ativos()
            ->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
            ->select('id', 'nome', 'sobrenome', 'email', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Presenças do dia usando scopes
        $presencasDoDia = Presenca::hoje()
            ->when($escolaId, function ($q) use ($escolaId) {
                $q->whereHas('aluno', fn($query) => $query->where('escola_id', $escolaId));
            })
            ->comRelacionamentos()
            ->select('id', 'aluno_id', 'funcionario_id', 'presente', 'hora_entrada', 'hora_saida', 'created_at')
            ->orderBy('hora_entrada', 'desc')
            ->limit(10)
            ->get();

        // Dados analíticos para gráficos (usar período unificado)
        $dadosAnaliticos = $this->getDadosAnaliticos($inicio, $fim);

        // Métricas financeiras (mês selecionado)
        // Suporta query param opcional mes=YYYY-MM; padrão: mês atual
        $mes = request('mes');
        if ($mes && preg_match('/^\d{4}-\d{2}$/', $mes)) {
            try {
                $inicioMes = Carbon::createFromFormat('Y-m', $mes)->startOfMonth();
                $fimMes = (clone $inicioMes)->endOfMonth();
            } catch (\Exception $e) {
                $inicioMes = Carbon::now()->startOfMonth();
                $fimMes = Carbon::now()->endOfMonth();
            }
        } else {
            $inicioMes = Carbon::now()->startOfMonth();
            $fimMes = Carbon::now()->endOfMonth();
        }

        // Receitas (modelo alinhado às Despesas):
        // - Total do mês: somatório de faturas com due_date no mês
        // - Recebido: faturas do mês marcadas como 'paid'
        // - Pendentes: total - recebido (considerando due_date do mês)
        // - Dinheiro/Gateway: apenas sobre as recebidas, dividido por método do pagamento

        // Período Financeiro (mês selecionado ou atual)
        $cashMethods = ['dinheiro', 'cash'];
        $gatewayMethods = ['pix', 'boleto', 'card', 'cartao', 'cartao_credito', 'credit_card'];

        // Cache key based on school and selected month
        $cacheKey = "dashboard_finance_{$escolaId}_" . ($mes ?: Carbon::now()->format('Y-m'));

        $financeData = Cache::remember($cacheKey, 300, function () use ($escolaId, $inicioMes, $fimMes, $cashMethods, $gatewayMethods) {
            // Total faturado no mês (por due_date)
            $totalFaturadoMesCents = Invoice::when($escolaId, fn($q) => $q->where('school_id', $escolaId))
                ->whereBetween('due_date', [$inicioMes, $fimMes])
                ->sum('total_cents');

            // Recebido no mês (por mês de pagamento): usar invoices com status 'paid' e paid_at no mês
            $receitasRecebidasMesCents = Invoice::when($escolaId, fn($q) => $q->where('school_id', $escolaId))
                ->where('status', 'paid')
                ->whereNotNull('paid_at')
                ->whereBetween('paid_at', [$inicioMes, $fimMes])
                ->sum('total_cents');

            // Pendentes no mês (por due_date): faturas não pagas nem canceladas
            $receitasPendentesMesCents = Invoice::when($escolaId, fn($q) => $q->where('school_id', $escolaId))
                ->whereBetween('due_date', [$inicioMes, $fimMes])
                ->whereNotIn('status', ['paid', 'canceled'])
                ->sum('total_cents');

            // Receitas recebidas por método (dinheiro vs gateway) no mês por due_date da fatura
            $receitaDinheiroCents = Payment::whereBetween('paid_at', [$inicioMes, $fimMes])
                ->whereIn(DB::raw('LOWER(status)'), ['received', 'confirmed'])
                ->whereIn(DB::raw('LOWER(method)'), array_map('strtolower', $cashMethods))
                ->whereHas('invoice', function ($q) use ($escolaId) {
                    if ($escolaId)
                        $q->where('school_id', $escolaId);
                })
                ->sum('net_amount_cents');

            $receitaGatewayCents = Payment::whereBetween('paid_at', [$inicioMes, $fimMes])
                ->whereIn(DB::raw('LOWER(status)'), ['received', 'confirmed'])
                ->whereIn(DB::raw('LOWER(method)'), array_map('strtolower', $gatewayMethods))
                ->whereHas('invoice', function ($q) use ($escolaId) {
                    if ($escolaId)
                        $q->where('school_id', $escolaId);
                })
                ->sum('net_amount_cents');

            // Recebidos totais (independente de mês) por escola
            $receitasRecebidasTotalCents = Payment::whereIn(DB::raw('LOWER(status)'), ['received', 'confirmed'])
                ->whereHas('invoice', function ($q) use ($escolaId) {
                    if ($escolaId)
                        $q->where('school_id', $escolaId);
                })
                ->sum('net_amount_cents');

            // Despesas do mês (liquidadas e pendentes)
            $despesaMensalLiquidadas = Despesa::when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
                ->whereBetween('data', [$inicioMes, $fimMes])
                ->where('status', 'liquidada')
                ->sum('valor');

            $despesaMensalPendentes = Despesa::when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
                ->whereBetween('data', [$inicioMes, $fimMes])
                ->where('status', 'pendente')
                ->sum('valor');

            $valorInadimplenciaCents = Invoice::when($escolaId, fn($q) => $q->where('school_id', $escolaId))
                ->whereBetween('due_date', [$inicioMes, $fimMes])
                ->whereDate('due_date', '<', Carbon::today())
                ->whereNotIn('status', ['paid', 'canceled'])
                ->sum('total_cents');

            $taxaInadimplenciaPercentual = $totalFaturadoMesCents > 0
                ? round(($valorInadimplenciaCents / $totalFaturadoMesCents) * 100, 1)
                : 0;

            return compact(
                'totalFaturadoMesCents',
                'receitasRecebidasMesCents',
                'receitasPendentesMesCents',
                'receitaDinheiroCents',
                'receitaGatewayCents',
                'receitasRecebidasTotalCents',
                'despesaMensalLiquidadas',
                'despesaMensalPendentes',
                'valorInadimplenciaCents',
                'taxaInadimplenciaPercentual'
            );
        });

        extract($financeData);

        // Tickets abertos (Conversas de suporte ativas)
        $ticketsQueryBase = Conversa::where('tipo', 'suporte')->where('ativo', true);
        if ($escolaId) {
            $ticketsQueryBase = $ticketsQueryBase->where(function ($q) use ($escolaId) {
                $q->whereHas('participantes', function ($qp) use ($escolaId) {
                    $qp->where('users.escola_id', $escolaId);
                })->orWhereHas('mensagens', function ($qm) use ($escolaId) {
                    $qm->whereHas('remetente', function ($qr) use ($escolaId) {
                        $qr->where('escola_id', $escolaId);
                    });
                });
            });
        }
        $ticketsAbertosCount = (clone $ticketsQueryBase)->count();

        // Séries para sparklines: usar últimos 12 meses + próximos 2 meses
        $inicioPeriodo = Carbon::now()->startOfMonth()->subMonths(11);
        $fimPeriodo = Carbon::now()->copy()->addMonths(2)->endOfMonth();

        // Grouped queries for all series at once
        $serieInadimplenciaRaw = Invoice::when($escolaId, fn($q) => $q->where('school_id', $escolaId))
            ->whereBetween('due_date', [Carbon::today()->subDays(6), Carbon::today()])
            ->whereNotIn('status', ['paid', 'canceled'])
            ->selectRaw('DATE(due_date) as data, COUNT(*) as valor')
            ->groupBy('data')
            ->toBase()
            ->get()->pluck('valor', 'data');

        $serieTicketsRaw = (clone $ticketsQueryBase)
            ->whereBetween('created_at', [Carbon::today()->subDays(6), Carbon::today()])
            ->selectRaw('DATE(created_at) as data, COUNT(*) as valor')
            ->groupBy('data')
            ->toBase()
            ->get()->pluck('valor', 'data');

        $serieReceitasRaw = Invoice::when($escolaId, fn($q) => $q->where('school_id', $escolaId))
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$inicioPeriodo, $fimPeriodo])
            ->selectRaw('DATE(paid_at) as data, SUM(total_cents) as valor')
            ->groupBy('data')
            ->toBase()
            ->get()->pluck('valor', 'data');

        $serieReceitasDinheiroRaw = Payment::whereIn(DB::raw('LOWER(status)'), ['received', 'confirmed'])
            ->whereBetween('paid_at', [$inicioPeriodo, $fimPeriodo])
            ->whereIn(DB::raw('LOWER(method)'), array_map('strtolower', $cashMethods))
            ->whereHas('invoice', function ($q) use ($escolaId) {
                if ($escolaId)
                    $q->where('school_id', $escolaId);
            })
            ->selectRaw('DATE(paid_at) as data, SUM(net_amount_cents) as valor')
            ->groupBy('data')
            ->toBase()
            ->get()->pluck('valor', 'data');

        $serieReceitasGatewayRaw = Payment::whereIn(DB::raw('LOWER(status)'), ['received', 'confirmed'])
            ->whereBetween('paid_at', [$inicioPeriodo, $fimPeriodo])
            ->whereIn(DB::raw('LOWER(method)'), array_map('strtolower', $gatewayMethods))
            ->whereHas('invoice', function ($q) use ($escolaId) {
                if ($escolaId)
                    $q->where('school_id', $escolaId);
            })
            ->selectRaw('DATE(paid_at) as data, SUM(net_amount_cents) as valor')
            ->groupBy('data')
            ->toBase()
            ->get()->pluck('valor', 'data');

        $serieReceitasTotalRaw = Invoice::when($escolaId, fn($q) => $q->where('school_id', $escolaId))
            ->whereBetween('due_date', [$inicioPeriodo, $fimPeriodo])
            ->selectRaw('DATE(due_date) as data, SUM(total_cents) as valor')
            ->groupBy('data')
            ->toBase()
            ->get()->pluck('valor', 'data');

        $serieReceitasPendentesRaw = Invoice::when($escolaId, fn($q) => $q->where('school_id', $escolaId))
            ->whereBetween('due_date', [$inicioPeriodo, $fimPeriodo])
            ->whereNotIn('status', ['paid', 'canceled'])
            ->selectRaw('DATE(due_date) as data, SUM(total_cents) as valor')
            ->groupBy('data')
            ->toBase()
            ->get()->pluck('valor', 'data');

        $serieDespesasLiquidadasRaw = Despesa::when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
            ->whereBetween('data', [$inicioPeriodo, $fimPeriodo])
            ->where('status', 'liquidada')
            ->selectRaw('DATE(data) as data, SUM(valor) as valor')
            ->groupBy('data')
            ->toBase()
            ->get()->pluck('valor', 'data');

        $serieDespesasPendentesRaw = Despesa::when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
            ->whereBetween('data', [$inicioPeriodo, $fimPeriodo])
            ->where('status', 'pendente')
            ->selectRaw('DATE(data) as data, SUM(valor) as valor')
            ->groupBy('data')
            ->toBase()
            ->get()->pluck('valor', 'data');

        $serieDespesasTotalRaw = Despesa::when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
            ->whereBetween('data', [$inicioPeriodo, $fimPeriodo])
            ->selectRaw('DATE(data) as data, SUM(valor) as valor')
            ->groupBy('data')
            ->toBase()
            ->get()->pluck('valor', 'data');

        // Helper to fill gaps for series
        $fillGaps = function ($inicio, $fim, $rawData, $isCents = true) {
            $serie = collect();
            $cursor = $inicio->copy();
            while ($cursor->lte($fim)) {
                $dataStr = $cursor->toDateString();
                $valor = $rawData->get($dataStr) ?? 0;
                $serie->push([
                    'data' => $dataStr,
                    'valor' => $isCents ? (int) $valor : (float) $valor,
                    'valor_cents' => $isCents ? (int) $valor : null
                ]);
                $cursor->addDay();
            }
            return $serie;
        };

        $diasUltimosSete = collect(range(0, 6))->map(fn($i) => Carbon::today()->subDays(6 - $i));
        $serieInadimplencia = collect($diasUltimosSete)->map(fn($dia) => ['data' => $dia->toDateString(), 'valor' => (int) ($serieInadimplenciaRaw->get($dia->toDateString()) ?? 0)]);
        $serieTickets = collect($diasUltimosSete)->map(fn($dia) => ['data' => $dia->toDateString(), 'valor' => (int) ($serieTicketsRaw->get($dia->toDateString()) ?? 0)]);

        // MRR Series (continues to be calculated per month for the last 6 months)
        $serieMrr = collect(range(0, 5))->map(function ($i) use ($escolaId) {
            $inicio = Carbon::now()->startOfMonth()->subMonths(5 - $i);
            $fim = (clone $inicio)->endOfMonth();
            $total = Subscription::when($escolaId, fn($q) => $q->where('school_id', $escolaId))
                ->where(function ($q) use ($inicio, $fim) {
                    $q->whereDate('start_at', '<=', $fim->toDateString())
                        ->where(function ($qq) use ($inicio) {
                            $qq->whereNull('end_at')->orWhereDate('end_at', '>=', $inicio->toDateString());
                        });
                })
                ->where('status', 'active')
                ->sum('amount_cents');
            return ['mes' => $inicio->format('Y-m'), 'valor_cents' => (int) $total];
        });

        // Fill gaps for long period series
        $diasPeriodo = collect();
        $cursor = $inicioPeriodo->copy();
        while ($cursor->lte($fimPeriodo)) {
            $diasPeriodo->push(clone $cursor);
            $cursor->addDay();
        }

        $serieReceitas = $fillGaps($inicioPeriodo, $fimPeriodo, $serieReceitasRaw);
        $serieReceitasDinheiro = $fillGaps($inicioPeriodo, $fimPeriodo, $serieReceitasDinheiroRaw);
        $serieReceitasGateway = $fillGaps($inicioPeriodo, $fimPeriodo, $serieReceitasGatewayRaw);
        $serieReceitasTotal = $fillGaps($inicioPeriodo, $fimPeriodo, $serieReceitasTotalRaw);
        $serieReceitasPendentes = $fillGaps($inicioPeriodo, $fimPeriodo, $serieReceitasPendentesRaw);
        $serieDespesasLiquidadas = $fillGaps($inicioPeriodo, $fimPeriodo, $serieDespesasLiquidadasRaw, false);
        $serieDespesasPendentes = $fillGaps($inicioPeriodo, $fimPeriodo, $serieDespesasPendentesRaw, false);
        $serieDespesas = $fillGaps($inicioPeriodo, $fimPeriodo, $serieDespesasTotalRaw, false);

        // MRR total
        $mrrCents = Subscription::when($escolaId, fn($q) => $q->where('school_id', $escolaId))
            ->where('status', 'active')
            ->sum('amount_cents');

        // Método predominante (mês selecionado)
        $metodoPredominante = Payment::whereBetween('paid_at', [$inicioMes, $fimMes])
            ->whereHas('invoice', function ($q) use ($escolaId) {
                if ($escolaId)
                    $q->where('school_id', $escolaId);
            })
            ->select('method', DB::raw('COUNT(*) as total'))
            ->groupBy('method')
            ->orderByDesc('total')
            ->first();

        $metodoPredominanteLabel = $metodoPredominante ? match (strtolower($metodoPredominante->method)) {
            'pix' => 'PIX',
            'boleto' => 'Boleto',
            'cartao', 'cartao_credito', 'credit_card' => 'Cartão',
            default => ucfirst($metodoPredominante->method)
        } : '—';

        // Recebimentos pendentes (lista): a vencer e vencidos
        $pendentesBase = Invoice::when($escolaId, fn($q) => $q->where('invoices.school_id', $escolaId))
            ->whereNotIn('invoices.status', ['paid', 'canceled'])
            ->leftJoin('subscriptions', 'invoices.subscription_id', '=', 'subscriptions.id')
            ->leftJoin('responsaveis', 'subscriptions.payer_id', '=', 'responsaveis.id');

        $pendentesAVencer = (clone $pendentesBase)
            ->whereDate('invoices.due_date', '>=', Carbon::today())
            ->orderBy('invoices.due_date', 'asc')
            ->limit(8)
            ->get([
                'invoices.id',
                'invoices.number',
                'invoices.due_date',
                'invoices.total_cents',
                'invoices.status',
                'invoices.gateway_alias',
                'responsaveis.nome as payer_nome',
                'responsaveis.sobrenome as payer_sobrenome'
            ]);

        $pendentesVencidas = (clone $pendentesBase)
            ->whereDate('invoices.due_date', '<', Carbon::today())
            ->orderBy('invoices.due_date', 'asc')
            ->limit(8)
            ->get([
                'invoices.id',
                'invoices.number',
                'invoices.due_date',
                'invoices.total_cents',
                'invoices.status',
                'invoices.gateway_alias',
                'responsaveis.nome as payer_nome',
                'responsaveis.sobrenome as payer_sobrenome'
            ]);

        $totalPendentesAVencerCents = (clone $pendentesBase)
            ->whereDate('invoices.due_date', '>=', Carbon::today())
            ->sum('invoices.total_cents');
        $totalPendentesVencidasCents = (clone $pendentesBase)
            ->whereDate('invoices.due_date', '<', Carbon::today())
            ->sum('invoices.total_cents');

        $pendentesTodos = (clone $pendentesBase)
            ->whereBetween('invoices.due_date', [$inicioPeriodo->toDateString(), $fimPeriodo->toDateString()])
            ->orderBy('invoices.due_date', 'asc')
            ->get([
                'invoices.id',
                'invoices.number',
                'invoices.due_date',
                'invoices.total_cents',
                'invoices.status',
                'invoices.gateway_alias',
                'responsaveis.nome as payer_nome',
                'responsaveis.sobrenome as payer_sobrenome'
            ]);

        // Despesas pendentes (lista): a vencer e vencidas
        $despesasPendentesBase = Despesa::when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
            ->where('status', 'pendente');

        $despesasPendAVencer = (clone $despesasPendentesBase)
            ->whereDate('data', '>=', Carbon::today())
            ->orderBy('data', 'asc')
            ->limit(8)
            ->get(['id', 'descricao', 'categoria', 'data', 'valor', 'status']);

        $despesasPendVencidas = (clone $despesasPendentesBase)
            ->whereDate('data', '<', Carbon::today())
            ->orderBy('data', 'asc')
            ->limit(8)
            ->get(['id', 'descricao', 'categoria', 'data', 'valor', 'status']);

        $totalDespesasPendAVencer = (clone $despesasPendentesBase)
            ->whereDate('data', '>=', Carbon::today())
            ->sum('valor');
        $totalDespesasPendVencidas = (clone $despesasPendentesBase)
            ->whereDate('data', '<', Carbon::today())
            ->sum('valor');

        $despesasPendTodos = (clone $despesasPendentesBase)
            ->whereBetween('data', [$inicioPeriodo->toDateString(), $fimPeriodo->toDateString()])
            ->orderBy('data', 'asc')
            ->get(['id', 'descricao', 'categoria', 'data', 'valor', 'status']);

        // Se for AJAX, retornar JSON com os dados analíticos e metadados do período
        if ($request->ajax() || $request->boolean('ajax')) {
            return response()->json([
                'dadosAnaliticos' => $dadosAnaliticos,
                'analytics' => [
                    'inicio' => $inicio->toDateString(),
                    'fim' => $fim->toDateString(),
                    'dias' => (int) ($inicio->diffInDays($fim) + 1),
                    'clamped' => $clamped,
                ],
            ]);
        }

        return view('dashboard', [
            'totalAlunos' => $estatisticas['totalAlunos'],
            'totalResponsaveis' => $estatisticas['totalResponsaveis'],
            'totalFuncionarios' => $estatisticas['totalFuncionarios'],
            'totalSalas' => $estatisticas['totalSalas'],
            'presencasHoje' => $presencasHoje,
            'faltasHoje' => $faltasHoje,
            'escalasHoje' => $escalasHoje,
            'ultimosAlunos' => $ultimosAlunos,
            'presencasDoDia' => $presencasDoDia,
            'presencasHojeDetalhes' => $presencasDoDia,
            'dadosAnaliticos' => $dadosAnaliticos,
            // Período Analytics para a view
            'analyticsInicio' => $inicio->toDateString(),
            'analyticsFim' => $fim->toDateString(),
            'analyticsDias' => (int) ($inicio->diffInDays($fim) + 1),
            'analyticsClamped' => $clamped,
            // Receitas (alinhado a Despesas)
            'receitasTotalMesCents' => $totalFaturadoMesCents,
            'receitasRecebidasMesCents' => $receitasRecebidasMesCents,
            'receitasRecebidasTotalCents' => $receitasRecebidasTotalCents,
            'receitasPendentesMesCents' => $receitasPendentesMesCents,
            'despesaMensalLiquidadas' => $despesaMensalLiquidadas,
            'despesaMensalPendentes' => $despesaMensalPendentes,
            // Financeiro adicional
            'valorInadimplenciaCents' => $valorInadimplenciaCents,
            'taxaInadimplenciaPercentual' => $taxaInadimplenciaPercentual,
            'serieInadimplencia' => $serieInadimplencia,
            'ticketsAbertosCount' => $ticketsAbertosCount,
            'serieTickets' => $serieTickets,
            'mrrCents' => $mrrCents,
            'serieMrr' => $serieMrr,
            'metodoPredominanteLabel' => $metodoPredominanteLabel,
            'serieReceitas' => $serieReceitas, // recebidas por due_date (net)
            'serieReceitasTotal' => $serieReceitasTotal,
            'serieReceitasPendentes' => $serieReceitasPendentes,
            'serieReceitasDinheiro' => $serieReceitasDinheiro,
            'serieReceitasGateway' => $serieReceitasGateway,
            'receitaDinheiroCents' => $receitaDinheiroCents, // mês atual (recebidas) por due_date
            'receitaGatewayCents' => $receitaGatewayCents, // mês atual (recebidas) por due_date
            'serieDespesas' => $serieDespesas,
            'serieDespesasLiquidadas' => $serieDespesasLiquidadas,
            'serieDespesasPendentes' => $serieDespesasPendentes,
            // Listas de recebimentos pendentes
            'pendentesAVencer' => $pendentesAVencer,
            'pendentesVencidas' => $pendentesVencidas,
            'totalPendentesAVencerCents' => $totalPendentesAVencerCents,
            'totalPendentesVencidasCents' => $totalPendentesVencidasCents,
            'pendentesTodos' => $pendentesTodos,
            // Listas de despesas pendentes
            'despesasPendAVencer' => $despesasPendAVencer,
            'despesasPendVencidas' => $despesasPendVencidas,
            'totalDespesasPendAVencer' => $totalDespesasPendAVencer,
            'totalDespesasPendVencidas' => $totalDespesasPendVencidas,
            'despesasPendTodos' => $despesasPendTodos,
        ]);
    }

    /**
     * Coleta dados analíticos para o dashboard
     */
    private function getDadosAnaliticos($inicio, $fim)
    {
        $hoje = Carbon::today();
        // Normalizar período recebido e unificar defaults para todas as métricas
        $inicio = ($inicio instanceof Carbon) ? $inicio->copy()->startOfDay() : Carbon::parse($inicio)->startOfDay();
        $fim = ($fim instanceof Carbon) ? $fim->copy()->endOfDay() : Carbon::parse($fim)->endOfDay();
        $ultimosSete = $inicio;
        $fimSete = $fim;
        $ultimosTrinta = $inicio;
        $fimTrinta = $fim;
        $ultimosQuinze = $inicio;
        $fimQuinze = $fim;

        // Determinar escola_id baseado no tipo de usuário
        if (auth()->user()->isSuperAdmin() || auth()->user()->temCargo('Suporte')) {
            $escolaId = session('escola_atual') ?: auth()->user()->escola_id;
        } else {
            $escolaId = auth()->user()->escola_id;
        }

        // Gráfico de presenças em período selecionado (ou últimos 7 dias)
        $presencasPorDia = Presenca::whereBetween('data', [$ultimosSete->toDateString(), $fimSete->toDateString()])
            ->when($escolaId, function ($q) use ($escolaId) {
                $q->whereHas('aluno', fn($query) => $query->where('escola_id', $escolaId));
            })
            ->selectRaw('DATE(data) as dia, COUNT(*) as total, SUM(CASE WHEN presente = true THEN 1 ELSE 0 END) as presentes')
            ->groupBy(\DB::raw('DATE(data)'))
            ->orderBy('dia')
            ->get()
            ->map(function ($r) {
                return (object) [
                    'data' => \Carbon\Carbon::parse($r->dia)->toDateString(),
                    'total' => (int) ($r->total ?? 0),
                    'presentes' => (int) ($r->presentes ?? 0),
                ];
            });

        // Presenças por sala no período (usando presencas.sala_id para manter histórico)
        $presencasPorSala = Presenca::whereBetween('data', [$ultimosSete->toDateString(), $fimSete->toDateString()])
            ->join('salas', 'presencas.sala_id', '=', 'salas.id')
            ->when($escolaId, function ($q) use ($escolaId) {
                $q->where('salas.escola_id', $escolaId);
            })
            ->whereNotNull('presencas.sala_id')
            ->where('salas.ativo', true)
            ->selectRaw('salas.nome as sala, COUNT(*) as total, SUM(CASE WHEN presencas.presente = true THEN 1 ELSE 0 END) as presentes')
            ->groupBy('salas.id', 'salas.nome')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Métricas de desempenho dos professores (período selecionado)
        $ultimosTrinta = $inicio;
        $fimTrinta = $fim;
        $professoresBase = Funcionario::ativos()
            ->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
            ->withCount([
                'escalas as total_escalas' => function ($query) use ($ultimosTrinta, $fimTrinta) {
                    $query->whereBetween('data', [$ultimosTrinta->toDateString(), $fimTrinta->toDateString()]);
                },
                'presencasRegistradas as presencas_registradas' => function ($query) use ($ultimosTrinta, $fimTrinta) {
                    $query->whereBetween('data', [$ultimosTrinta->toDateString(), $fimTrinta->toDateString()]);
                }
            ])
            ->get();

        $professoresComAtividade = $professoresBase->filter(function ($funcionario) {
            return $funcionario->total_escalas > 0;
        });
        $totalProfessoresComAtividade = $professoresComAtividade->count();
        $desempenhoProfessores = $professoresComAtividade->sortByDesc('total_escalas')->take(5);

        // Alertas de baixa frequência (período selecionado)
        $ultimosQuinze = $inicio;
        $fimQuinze = $fim;
        $alertasBaixaFrequencia = Aluno::ativos()
            ->when($escolaId, fn($q) => $q->where('escola_id', $escolaId))
            ->withCount([
                'presencas as total_registros' => function ($query) use ($ultimosQuinze, $fimQuinze) {
                    $query->whereBetween('data', [$ultimosQuinze->toDateString(), $fimQuinze->toDateString()]);
                },
                'presencas as presencas_confirmadas' => function ($query) use ($ultimosQuinze, $fimQuinze) {
                    $query->whereBetween('data', [$ultimosQuinze->toDateString(), $fimQuinze->toDateString()])
                        ->where('presente', true);
                }
            ])
            ->get()
            ->map(function ($aluno) {
                $total = (int) ($aluno->total_registros ?? 0);
                $confirmadas = (int) ($aluno->presencas_confirmadas ?? 0);
                $aluno->frequencia = $total > 0 ? round(($confirmadas / $total) * 100, 1) : 0.0;
                return $aluno;
            })
            ->filter(function ($aluno) {
                return ($aluno->total_registros ?? 0) > 0 && ($aluno->frequencia ?? 0) < 70;
            })
            ->sortBy('frequencia')
            ->take(10)
            ->values();

        // Taxa de presença geral (período selecionado)
        $taxaPresencaGeral = Presenca::whereBetween('data', [$inicio->toDateString(), $fim->toDateString()])
            ->when($escolaId, function ($q) use ($escolaId) {
                $q->whereHas('aluno', fn($query) => $query->where('escola_id', $escolaId));
            })
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN presente = true THEN 1 ELSE 0 END) as presentes')
            ->first();

        $taxaPresencaPercentual = $taxaPresencaGeral->total > 0 ?
            round(($taxaPresencaGeral->presentes / $taxaPresencaGeral->total) * 100, 1) : 0;

        // Normalizar série de dias garantindo presença de todos os dias do período
        $periodoInicio = $ultimosSete->copy();
        $periodoFim = $fimSete->copy();
        $diasSerie = collect();
        $cursor = $periodoInicio->copy();
        while ($cursor->lte($periodoFim)) {
            $diasSerie->push((object) ['data' => $cursor->toDateString(), 'total' => 0, 'presentes' => 0]);
            $cursor->addDay();
        }
        $presencasPorDiaIndex = $presencasPorDia->keyBy('data');
        $presencasPorDiaNormalizado = $diasSerie->map(function ($dia) use ($presencasPorDiaIndex) {
            if ($presencasPorDiaIndex->has($dia->data)) {
                $reg = $presencasPorDiaIndex->get($dia->data);
                return (object) [
                    'data' => $dia->data,
                    'total' => (int) $reg->total,
                    'presentes' => (int) ($reg->presentes ?? 0)
                ];
            }
            return $dia;
        });

        return [
            'presencasPorDia' => $presencasPorDiaNormalizado,
            'presencasPorSala' => $presencasPorSala,
            'desempenhoProfessores' => $desempenhoProfessores,
            'alertasBaixaFrequencia' => $alertasBaixaFrequencia,
            'taxaPresencaGeral' => $taxaPresencaPercentual,
            'totalProfessoresComAtividade' => $totalProfessoresComAtividade,
        ];
    }
}
