<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Finance\Subscription;
use App\Models\Finance\BillingPlan;
use App\Models\Finance\Invoice;
use Carbon\Carbon;

class BillingGenerateInvoices extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'billing:generate-invoices
        {--month= : Mês alvo no formato YYYY-MM}
        {--school_id= : Gerar apenas para uma escola específica}
        {--plan_id= : Gerar apenas para um plano específico}
        {--dry-run : Apenas simular sem criar faturas}';

    /**
     * The console command description.
     */
    protected $description = 'Gera faturas mensais para assinaturas ativas, evitando duplicidades';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $monthOpt = $this->option('month');
        $dryRun = (bool) $this->option('dry-run');
        $schoolIdOpt = $this->option('school_id') ? (int)$this->option('school_id') : null;
        $planIdOpt = $this->option('plan_id') ? (int)$this->option('plan_id') : null;

        try {
            $target = $monthOpt ? Carbon::createFromFormat('Y-m', $monthOpt)->startOfMonth() : Carbon::now()->startOfMonth();
        } catch (\Throwable $e) {
            $this->error('Formato de mês inválido. Use YYYY-MM. Ex.: 2025-11');
            return 1;
        }

        $start = $target->copy();
        $end = $target->copy()->endOfMonth();

        $this->info(sprintf('Gerando faturas para %s%s%s%s',
            $target->format('Y-m'),
            $dryRun ? ' (dry-run)' : '',
            $schoolIdOpt ? " | escola_id={$schoolIdOpt}" : '',
            $planIdOpt ? " | billing_plan_id={$planIdOpt}" : ''
        ));

        $query = Subscription::query()
            ->where('status', 'active')
            ->whereDate('start_at', '<=', $end)
            ->where(function ($q) use ($start) {
                $q->whereNull('end_at')->orWhereDate('end_at', '>=', $start);
            });

        if ($schoolIdOpt) {
            $query->where('school_id', $schoolIdOpt);
        }
        if ($planIdOpt) {
            $query->where('billing_plan_id', $planIdOpt);
        }

        $total = 0;
        $created = 0;
        $duplicated = 0;
        $skippedInactivePlan = 0;

        $query->orderBy('id')->chunk(200, function ($subs) use ($start, $end, $dryRun, &$total, &$created, &$duplicated, &$skippedInactivePlan) {
            foreach ($subs as $sub) {
                $total++;
                $plan = BillingPlan::find($sub->billing_plan_id);
                if (!$plan || !$plan->active) {
                    $skippedInactivePlan++;
                    continue;
                }
                $day = max(1, min((int)($plan->day_of_month ?? 5), 28));
                $dueDate = $start->copy()->day($day);

                // Evitar duplicidades: existe fatura no mês para a assinatura
                $exists = Invoice::query()
                    ->where('subscription_id', $sub->id)
                    ->whereDate('due_date', '>=', $start)
                    ->whereDate('due_date', '<=', $end)
                    ->exists();
                if ($exists) {
                    $duplicated++;
                    continue;
                }

                // Calcular valor com desconto
                $amount = (int) ($plan->amount_cents ?? 0);
                $discount = (int) ($sub->discount_percent ?? 0);
                $net = max(0, (int) floor($amount * (100 - $discount) / 100));

                if ($dryRun) {
                    $this->line(sprintf('[DRY] Sub#%d Escola#%d Plano#%d => due=%s total=%d',
                        $sub->id, $sub->school_id, $sub->billing_plan_id, $dueDate->toDateString(), $net));
                    continue;
                }

                $invoice = new Invoice([
                    'subscription_id' => $sub->id,
                    'due_date' => $dueDate->toDateString(),
                    'total_cents' => $net,
                    'currency' => $plan->currency ?? 'BRL',
                    'status' => 'pending',
                ]);
                $invoice->school_id = (int) $sub->school_id;
                $invoice->number = $this->generateNumber((int) $sub->school_id);
                $invoice->save();
                $created++;
            }
        });

        $this->newLine();
        $this->info('Resumo:');
        $this->line("Assinaturas processadas: {$total}");
        $this->line("Faturas criadas: {$created}");
        $this->line("Duplicadas/Existentes: {$duplicated}");
        $this->line("Ignoradas por plano inativo: {$skippedInactivePlan}");
        $this->newLine();
        $this->info('Concluído.');
        return 0;
    }

    private function generateNumber(int $schoolId): string
    {
        $last = Invoice::where('school_id', $schoolId)->orderByDesc('id')->first();
        $next = $last ? ((int)preg_replace('/\D/', '', (string)$last->number)) + 1 : 1;
        return sprintf('F%06d', $next);
    }
}