<?php

namespace App\Jobs;

use App\Models\Finance\FinanceSettings;
use App\Models\Finance\Invoice;
use App\Models\Finance\DunningLog;
use App\Mail\DunningReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessDunningNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $all = FinanceSettings::all();
        foreach ($all as $settings) {
            $sch = $settings->dunning_schedule ?? [];
            if (empty($sch) || empty($sch['enabled'])) {
                continue;
            }

            $tz = $settings->timezone ?: 'America/Sao_Paulo';
            $now = Carbon::now($tz);

            // Verificar se o dia atual está habilitado
            $dayMap = [
                0 => 'dom',
                1 => 'seg',
                2 => 'ter',
                3 => 'qua',
                4 => 'qui',
                5 => 'sex',
                6 => 'sab'
            ];
            $currentDaySlug = $dayMap[$now->dayOfWeek];
            $enabledDays = $sch['days_of_week'] ?? [];
            if (!in_array($currentDaySlug, $enabledDays)) {
                continue;
            }

            // Verificar janelas de tempo
            $windows = $sch['time_windows'] ?? [];
            $inWindow = false;
            $currentTime = $now->format('H:i');
            foreach ($windows as $w) {
                if ($currentTime >= ($w['start'] ?? '08:00') && $currentTime <= ($w['end'] ?? '18:00')) {
                    $inWindow = true;
                    break;
                }
            }
            if (!$inWindow) {
                continue;
            }

            $this->processSchool($settings, $now);
        }
    }

    protected function processSchool(FinanceSettings $settings, Carbon $now): void
    {
        $sch = $settings->dunning_schedule;
        $schoolId = $settings->school_id;
        $throttle = $sch['throttle_per_run'] ?? 50;
        $sentCount = 0;

        // 1. Offsets Antes do Vencimento (Pre-due)
        $preOffsets = $sch['pre_due_offsets'] ?? [];
        foreach ($preOffsets as $days) {
            if ($sentCount >= $throttle)
                break;

            $targetDate = $now->copy()->addDays((int) $days)->format('Y-m-d');
            $invoices = Invoice::where('school_id', $schoolId)
                ->where('status', 'pending')
                ->whereDate('due_date', $targetDate)
                ->get();

            foreach ($invoices as $invoice) {
                if ($sentCount >= $throttle)
                    break;
                if ($this->shouldSend($invoice, 'pre', $days, $sch['channels'] ?? ['email'])) {
                    $this->dispatchNotifications($invoice, 'pre', $days, $sch['channels'] ?? ['email']);
                    $sentCount++;
                }
            }
        }

        // 2. No Dia do Vencimento (Due Day)
        if (!empty($sch['due_day']) && $sentCount < $throttle) {
            $targetDate = $now->format('Y-m-d');
            $invoices = Invoice::where('school_id', $schoolId)
                ->where('status', 'pending')
                ->whereDate('due_date', $targetDate)
                ->get();

            foreach ($invoices as $invoice) {
                if ($sentCount >= $throttle)
                    break;
                if ($this->shouldSend($invoice, 'due', 0, $sch['channels'] ?? ['email'])) {
                    $this->dispatchNotifications($invoice, 'due', 0, $sch['channels'] ?? ['email']);
                    $sentCount++;
                }
            }
        }

        // 3. Offsets Após o Vencimento (Overdue)
        $overdueOffsets = $sch['overdue_offsets'] ?? [];
        foreach ($overdueOffsets as $days) {
            if ($sentCount >= $throttle)
                break;

            $targetDate = $now->copy()->subDays((int) $days)->format('Y-m-d');
            $invoices = Invoice::where('school_id', $schoolId)
                ->where('status', 'overdue')
                ->whereDate('due_date', $targetDate)
                ->get();

            foreach ($invoices as $invoice) {
                if ($sentCount >= $throttle)
                    break;
                if ($this->shouldSend($invoice, 'post', $days, $sch['channels'] ?? ['email'])) {
                    $this->dispatchNotifications($invoice, 'post', $days, $sch['channels'] ?? ['email']);
                    $sentCount++;
                }
            }
        }

        if ($sentCount > 0) {
            Log::info("ProcessDunningNotifications: Sent $sentCount notifications for school $schoolId");
        }
    }

    protected function shouldSend(Invoice $invoice, string $type, int $days, array $channels): bool
    {
        // Se já foi enviado para PELO MENOS UM dos canais solicitados, evitamos o envio em lote
        // Para simplificar, verificamos se já existe log para este invoice/tipo/offset
        return !DunningLog::where('invoice_id', $invoice->id)
            ->where('offset_type', $type)
            ->where('offset_days', $days)
            ->exists();
    }

    protected function dispatchNotifications(Invoice $invoice, string $type, int $days, array $channels): void
    {
        $payer = $invoice->subscription->payer ?? null;
        if (!$payer || empty($payer->email)) {
            Log::warning("ProcessDunningNotifications: Payer not found or no email for invoice {$invoice->id}");
            return;
        }

        foreach ($channels as $channel) {
            if ($channel === 'email') {
                $this->sendEmail($invoice, $payer, $type, $days);
            }
            // WhatsApp TODO: Integrar com serviço de WhatsApp
        }
    }

    protected function sendEmail(Invoice $invoice, $payer, string $type, int $days): void
    {
        $subject = 'Lembrete de Pagamento';
        if ($type === 'pre') {
            $subject = "Sua fatura vence em $days " . ($days > 1 ? 'dias' : 'dia');
        } elseif ($type === 'post') {
            $subject = "Sua fatura está atrasada há $days " . ($days > 1 ? 'dias' : 'dia');
        } elseif ($type === 'due') {
            $subject = "Sua fatura vence hoje";
        }

        $payload = [
            'invoice' => $invoice,
            'payer' => $payer,
            'subject' => $subject,
            'type' => $type,
            'days' => $days,
        ];

        try {
            Mail::to($payer->email)->send(new DunningReminder($payload));

            DunningLog::create([
                'school_id' => $invoice->school_id,
                'invoice_id' => $invoice->id,
                'offset_type' => $type,
                'offset_days' => $days,
                'channel' => 'email',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("ProcessDunningNotifications: Failed to send email for invoice {$invoice->id}: " . $e->getMessage());
        }
    }
}