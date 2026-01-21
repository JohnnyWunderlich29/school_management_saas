<?php

namespace Tests\Feature;

use App\Jobs\ProcessDunningNotifications;
use App\Models\Escola;
use App\Models\Finance\FinanceSettings;
use App\Models\Finance\Invoice;
use App\Models\Finance\Subscription;
use App\Models\Responsavel;
use App\Models\Finance\DunningLog;
use App\Models\Finance\BillingPlan;
use App\Models\Finance\ChargeMethod;
use App\Mail\DunningReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class DunningNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected Escola $school;
    protected FinanceSettings $settings;
    protected Responsavel $payer;
    protected BillingPlan $plan;
    protected ChargeMethod $method;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = Escola::create(['nome' => 'Test School']);
        $this->settings = FinanceSettings::create([
            'school_id' => $this->school->id,
            'dunning_schedule' => [
                'enabled' => true,
                'timezone' => 'UTC',
                'days_of_week' => ['seg', 'ter', 'qua', 'qui', 'sex', 'sab', 'dom'],
                'time_windows' => [['start' => '00:00', 'end' => '23:59']],
                'pre_due_offsets' => [7, 1],
                'overdue_offsets' => [1, 5],
                'due_day' => true,
                'channels' => ['email'],
                'throttle_per_run' => 10,
            ]
        ]);

        $this->payer = Responsavel::create([
            'escola_id' => $this->school->id,
            'nome' => 'Test',
            'sobrenome' => 'Payer',
            'email' => 'payer@test.com',
            'cpf' => '12345678901',
            'telefone_principal' => '11999999999',
            'endereco' => 'Rua Teste',
            'cidade' => 'Cidade',
            'estado' => 'SP',
            'cep' => '12345-678',
            'parentesco' => 'Pai'
        ]);

        $this->plan = BillingPlan::create([
            'school_id' => $this->school->id,
            'name' => 'Monthly Plan',
            'amount_cents' => 1000,
            'currency' => 'BRL',
            'periodicity' => 'monthly',
            'active' => true
        ]);

        $this->method = ChargeMethod::create([
            'school_id' => $this->school->id,
            'name' => 'Boleto',
            'method' => 'boleto',
            'gateway_alias' => 'asaas',
            'active' => true
        ]);
    }

    public function test_sends_pre_due_notification(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-01-20 10:00:00');

        $sub = Subscription::create([
            'school_id' => $this->school->id,
            'payer_id' => $this->payer->id,
            'billing_plan_id' => $this->plan->id,
            'charge_method_id' => $this->method->id,
            'amount_cents' => 1000,
            'status' => 'active',
            'start_at' => '2026-01-01'
        ]);

        Invoice::create([
            'school_id' => $this->school->id,
            'subscription_id' => $sub->id,
            'due_date' => '2026-01-27',
            'total_cents' => 1000,
            'status' => 'pending'
        ]);

        (new ProcessDunningNotifications())->handle();

        Mail::assertSent(DunningReminder::class, function ($mail) {
            return $mail->hasTo('payer@test.com') &&
                $mail->payload['type'] === 'pre' &&
                $mail->payload['days'] === 7;
        });
    }

    public function test_sends_due_day_notification(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-01-20 10:00:00');

        $sub = Subscription::create([
            'school_id' => $this->school->id,
            'payer_id' => $this->payer->id,
            'billing_plan_id' => $this->plan->id,
            'charge_method_id' => $this->method->id,
            'amount_cents' => 1000,
            'status' => 'active',
            'start_at' => '2026-01-01'
        ]);

        Invoice::create([
            'school_id' => $this->school->id,
            'subscription_id' => $sub->id,
            'due_date' => '2026-01-20',
            'total_cents' => 1000,
            'status' => 'pending'
        ]);

        (new ProcessDunningNotifications())->handle();

        Mail::assertSent(DunningReminder::class, function ($mail) {
            return $mail->payload['type'] === 'due';
        });
    }

    public function test_sends_overdue_notification(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-01-20 10:00:00');

        $sub = Subscription::create([
            'school_id' => $this->school->id,
            'payer_id' => $this->payer->id,
            'billing_plan_id' => $this->plan->id,
            'charge_method_id' => $this->method->id,
            'amount_cents' => 1000,
            'status' => 'active',
            'start_at' => '2026-01-01'
        ]);

        Invoice::create([
            'school_id' => $this->school->id,
            'subscription_id' => $sub->id,
            'due_date' => '2026-01-19',
            'total_cents' => 1000,
            'status' => 'overdue'
        ]);

        (new ProcessDunningNotifications())->handle();

        Mail::assertSent(DunningReminder::class, function ($mail) {
            return $mail->payload['type'] === 'post' &&
                $mail->payload['days'] === 1;
        });
    }

    public function test_does_not_send_duplicate_notifications(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-01-20 10:00:00');

        $sub = Subscription::create([
            'school_id' => $this->school->id,
            'payer_id' => $this->payer->id,
            'billing_plan_id' => $this->plan->id,
            'charge_method_id' => $this->method->id,
            'amount_cents' => 1000,
            'status' => 'active',
            'start_at' => '2026-01-01'
        ]);

        $invoice = Invoice::create([
            'school_id' => $this->school->id,
            'subscription_id' => $sub->id,
            'due_date' => '2026-01-27',
            'total_cents' => 1000,
            'status' => 'pending'
        ]);

        DunningLog::create([
            'school_id' => $this->school->id,
            'invoice_id' => $invoice->id,
            'offset_type' => 'pre',
            'offset_days' => 7,
            'channel' => 'email',
            'sent_at' => now()
        ]);

        (new ProcessDunningNotifications())->handle();

        Mail::assertNothingSent();
    }
}
