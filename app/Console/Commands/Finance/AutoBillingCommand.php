<?php

namespace App\Console\Commands\Finance;

use Illuminate\Console\Command;
use App\Models\Finance\BillingAutomation;
use App\Models\Finance\Subscription;
use App\Models\Finance\Invoice;
use App\Models\Finance\FinanceSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AutoBillingCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'finance:auto-billing
        {--school_id= : Gerar apenas para uma escola específica}
        {--force : Ignorar configurações de ativação e dias de antecedência (processa hoje)}
        {--dry-run : Apenas simular sem criar faturas}';

    /**
     * The console command description.
     */
    protected $description = 'Geração automática de faturas para assinaturas recorrentes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $schoolIdOpt = $this->option('school_id') ? (int) $this->option('school_id') : null;
        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $this->info('Iniciando Automação de Faturamento...');

        $automationQuery = BillingAutomation::query();
        if ($schoolIdOpt) {
            $automationQuery->where('school_id', $schoolIdOpt);
        }
        if (!$force) {
            $automationQuery->where('active', true);
        }

        $automations = $automationQuery->get();

        if ($automations->isEmpty()) {
            $this->warn('Nenhuma automação ativa encontrada.');
            return 0;
        }

        foreach ($automations as $automation) {
            $this->processSchool($automation, $force, $dryRun);
        }

        $this->info('Processamento concluído.');
        return 0;
    }

    private function processSchool(BillingAutomation $automation, bool $force, bool $dryRun)
    {
        $schoolId = $automation->school_id;
        $daysAdvance = $force ? 0 : (int) $automation->days_advance;
        $targetDate = Carbon::now()->addDays($daysAdvance);

        $this->info("Escola #$schoolId: Processando faturas até {$targetDate->toDateString()} (Antecedência: $daysAdvance dias)");

        // 1. Buscar assinaturas que precisam ser faturadas
        // Precisam estar ativas
        // start_at <= targetDate
        // (Null end_at ou end_at >= targetDate)
        // last_billed_at < Próximo vencimento calculado

        $subsQuery = Subscription::where('school_id', $schoolId)
            ->where('status', 'active')
            ->whereDate('start_at', '<=', $targetDate)
            ->where(function ($q) use ($targetDate) {
                $q->whereNull('end_at')->orWhereDate('end_at', '>=', $targetDate->copy()->startOfMonth());
            });

        $subscriptions = $subsQuery->get();
        $toBill = [];

        foreach ($subscriptions as $sub) {
            $nextDueDate = $this->calculateNextDueDate($sub);

            if ($nextDueDate && $nextDueDate->lessThanOrEqualTo($targetDate)) {
                $key = $automation->consolidate_default ? "payer_{$sub->payer_id}" : "sub_{$sub->id}";
                $toBill[$key][] = [
                    'sub' => $sub,
                    'dueDate' => $nextDueDate
                ];
            }
        }

        if (empty($toBill)) {
            $this->line(" - Nenhuma fatura pendente.");
            return;
        }

        foreach ($toBill as $key => $items) {
            $this->generateInvoicesForItems($items, $automation, $dryRun);
        }
    }

    private function calculateNextDueDate(Subscription $sub): ?Carbon
    {
        // Se nunca faturou, o primeiro vencimento é baseado no start_at e day_of_month
        $baseDate = $sub->last_billed_at ? Carbon::parse($sub->last_billed_at)->addMonth()->startOfMonth() : Carbon::parse($sub->start_at)->startOfMonth();

        $day = (int) ($sub->day_of_month ?? 5);
        $daysInMonth = $baseDate->daysInMonth;
        $targetDay = min($day, $daysInMonth);

        return $baseDate->copy()->day($targetDay);
    }

    private function generateInvoicesForItems(array $items, BillingAutomation $automation, bool $dryRun)
    {
        $schoolId = $automation->school_id;
        $payerId = $items[0]['sub']->payer_id;

        // Se consolidado, usamos a data de vencimento do primeiro item (ou a maior?)
        // Na prática, se for mensal, todos devem cair no mesmo mês.
        $dueDate = $items[0]['dueDate'];

        $totalCents = 0;
        $subIds = [];
        $notes = [];

        foreach ($items as $item) {
            $sub = $item['sub'];
            $amount = (int) ($sub->amount_cents ?? 0);
            $discount = (int) ($sub->discount_percent ?? 0);
            $net = max(0, (int) floor($amount * (100 - $discount) / 100));

            $totalCents += $net;
            $subIds[] = $sub->id;

            $subNote = $sub->description ?: "Assinatura #{$sub->id}";
            if ($discount > 0) {
                $subNote .= " (Desc. {$discount}%)";
            }

            // Lógica de desconto antecipado
            if ($sub->early_discount_active && $sub->early_discount_value > 0) {
                $limitDate = $dueDate->copy()->subDays((int) $sub->early_discount_days);
                $subNote .= " - Desc. Antecipado {$sub->early_discount_value}% até {$limitDate->format('d/m/Y')}";
            }

            $notes[] = $subNote;
        }

        $finalNote = implode("\n", $notes);

        if ($dryRun) {
            $this->line(" [DRY] Payer #$payerId: Criando fatura de R$ " . number_format($totalCents / 100, 2, ',', '.') . " para Subs: " . implode(',', $subIds));
            return;
        }

        try {
            DB::beginTransaction();

            $invoice = new Invoice([
                'school_id' => $schoolId,
                'payer_id' => $payerId,
                'subscription_id' => count($subIds) === 1 ? $subIds[0] : null, // Se consolidado, single sub_id é nulo ou do primeiro? Deixo nulo se for multi
                'due_date' => $dueDate->toDateString(),
                'total_cents' => $totalCents,
                'currency' => 'BRL',
                'status' => 'pending',
                'notes' => $finalNote,
            ]);

            $invoice->number = $this->generateNumber($schoolId);
            $invoice->save();

            // Vincular à tabela pivot se existir (ou se usarmos sub_id opcional)
            // No schema atual, Invoice tem subscription_id. Se for consolidado, talvez precisemos de uma tabela pivot ou apenas aceitar que subscription_id pode ser nulo.

            // Atualizar last_billed_at das assinaturas
            foreach ($items as $item) {
                $item['sub']->last_billed_at = $dueDate->toDateString();
                $item['sub']->save();
            }

            DB::commit();
            $this->line(" + Fatura #$invoice->number criada para Payer #$payerId (Total: R$ " . number_format($totalCents / 100, 2, ',', '.') . ")");

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Erro ao gerar fatura para Payer #$payerId: " . $e->getMessage());
            Log::error("AutoBilling error", ['payer_id' => $payerId, 'error' => $e->getMessage()]);
        }
    }

    private function generateNumber(int $schoolId): string
    {
        $last = Invoice::where('school_id', $schoolId)->orderByDesc('id')->first();
        $next = $last ? ((int) preg_replace('/\D/', '', (string) $last->number)) + 1 : 1;
        return sprintf('F%06d', $next);
    }
}
