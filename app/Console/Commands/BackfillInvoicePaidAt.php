<?php

namespace App\Console\Commands;

use App\Models\Finance\Invoice;
use App\Models\Finance\Payment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class BackfillInvoicePaidAt extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'finance:backfill-invoice-paid-at
        {--strategy=min : min|max - usar menor (primeiro) ou maior (último) paid_at dos payments}
        {--school_id= : ID da escola para filtrar}
        {--chunk=500 : Tamanho do lote para processamento}
        {--dry-run : Executa sem salvar alterações}
        {--overwrite : Recalcula mesmo se a fatura já possuir paid_at}
    ';

    /**
     * The console command description.
     */
    protected $description = 'Backfill de invoices.paid_at a partir de payments.paid_at (confirmados), com estratégia min|max e filtros opcionais.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $strategy = strtolower((string) $this->option('strategy')) ?: 'min';
        if (!in_array($strategy, ['min', 'max'], true)) {
            $this->error('Estratégia inválida. Use min ou max.');
            return self::INVALID;
        }

        $schoolId = $this->option('school_id');
        $chunk = (int) ($this->option('chunk') ?? 500);
        $dryRun = (bool) $this->option('dry-run');
        $overwrite = (bool) $this->option('overwrite');

        $statuses = ['confirmed', 'received'];

        $query = Invoice::query()
            ->where('status', 'paid');

        if (!$overwrite) {
            $query->whereNull('paid_at');
        }

        if (!empty($schoolId)) {
            $query->where('school_id', $schoolId);
        }

        $totalAlvos = (clone $query)->count();
        if ($totalAlvos === 0) {
            $this->info('Nenhuma fatura elegível para backfill.');
            return self::SUCCESS;
        }

        $this->info("Processando {$totalAlvos} faturas com strategy={$strategy}, chunk={$chunk}, overwrite=" . ($overwrite ? 'true' : 'false') . (empty($schoolId) ? '' : ", school_id={$schoolId}") . ($dryRun ? ' [dry-run]' : ''));

        $atualizados = 0;
        $semPayments = 0;

        $query->orderBy('id')
            ->chunkById($chunk, function (Collection $invoices) use (&$atualizados, &$semPayments, $statuses, $strategy, $dryRun) {
                $ids = $invoices->pluck('id')->all();

                $payments = Payment::query()
                    ->whereIn('invoice_id', $ids)
                    ->whereIn('status', $statuses)
                    ->get(['invoice_id', 'paid_at', 'updated_at'])
                    ->groupBy('invoice_id');

                foreach ($invoices as $invoice) {
                    /** @var \App\Models\Finance\Invoice $invoice */
                    $pays = $payments->get($invoice->id, collect());

                    $candidatos = $pays
                        ->map(function ($pay) {
                            // Usa paid_at do payment se houver; senão usa updated_at do payment
                            $ts = $pay->paid_at ?: $pay->updated_at;
                            return $ts ? Carbon::parse($ts) : null;
                        })
                        ->filter();

                    // Se não houver payments, podemos cair para updated_at da fatura
                    if ($candidatos->isEmpty()) {
                        $semPayments++;
                        $fallback = $invoice->updated_at ? Carbon::parse($invoice->updated_at) : null;
                        if (!$fallback) {
                            continue;
                        }
                        $alvo = $fallback;
                    } else {
                        $alvo = $strategy === 'min' ? $candidatos->min() : $candidatos->max();
                    }

                    // Evita sobrescrever com o mesmo valor
                    $alvoStr = $alvo->toDateTimeString();
                    if ($invoice->paid_at && Carbon::parse($invoice->paid_at)->toDateTimeString() === $alvoStr) {
                        continue;
                    }

                    $this->line("Invoice #{$invoice->id}: paid_at " . ($invoice->paid_at ? Carbon::parse($invoice->paid_at)->toDateTimeString() : 'null') . " => {$alvoStr}");

                    if (!$dryRun) {
                        $invoice->paid_at = $alvo;
                        // Não alterar updated_at para não poluir histórico
                        $invoice->timestamps = false;
                        $invoice->save();
                    }
                    $atualizados++;
                }
            });

        $this->info("Atualizados: {$atualizados} | Sem payments: {$semPayments}" . ($dryRun ? ' [dry-run]' : ''));
        return self::SUCCESS;
    }
}