<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

\Illuminate\Support\Facades\Schedule::job(new \App\Jobs\ProcessDunningNotifications())->everyFiveMinutes()->name('finance_dunning');
\Illuminate\Support\Facades\Schedule::command('finance:gerar-despesas-recorrentes')->dailyAt('03:00')->name('finance_recurring_expenses');

