<?php

namespace App\Jobs;

use App\Models\Finance\FinanceSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessDunningNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        // You can pass context here if needed
    }

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

            $dayMap = [
                1 => 'seg', 2 => 'ter', 3 => 'qua', 4 => 'qui', 5 => 'sex', 6 => 'sab', 0 => 'dom',
            ];
            $dow = $dayMap[(int)$now->dayOfWeek];
            $days = $sch['days_of_week'] ?? [];
            if (!in_array($dow, $days, true)) {
                continue;
            }

            $windows = $sch['time_windows'] ?? [];
            $within = false;
            foreach ($windows as $w) {
                $startStr = $w['start'] ?? null;
                $endStr = $w['end'] ?? null;
                if (!$startStr || !$endStr) continue;
                try {
                    $start = Carbon::createFromFormat('H:i', $startStr, $tz)->setDate($now->year, $now->month, $now->day);
                    $end = Carbon::createFromFormat('H:i', $endStr, $tz)->setDate($now->year, $now->month, $now->day);
                    if ($now->between($start, $end)) { $within = true; break; }
                } catch (\Throwable $e) {
                    // Ignore malformed window
                }
            }
            if (!$within) continue;

            Log::info('ProcessDunningNotifications tick', [
                'school_id' => $settings->school_id,
                'time' => $now->toIso8601String(),
                'throttle' => $sch['throttle_per_run'] ?? 50,
                'channels' => $sch['channels'] ?? ['email'],
            ]);

            // TODO: Implement invoice selection and dispatch mail/whatsapp messages.
        }
    }
}