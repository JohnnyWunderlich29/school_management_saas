<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Escola;
use App\Models\Finance\Invoice;
use App\Models\Finance\Payment;
use App\Models\Finance\FinanceGateway;
use App\Models\Finance\BillingPlan;
use App\Models\Finance\Subscription;

class FinanceWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected Escola $escola;
    protected FinanceGateway $gateway;
    protected BillingPlan $plan;
    protected ?Subscription $subscription = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Criar escola mínima
        $this->escola = Escola::create([
            'nome' => 'Escola Webhook',
            'cnpj' => '12.345.678/0001-99',
            'razao_social' => 'Escola Webhook LTDA',
            'email' => 'finance@escola.com',
            'telefone' => '(11) 9999-9999',
            'endereco' => 'Rua Teste',
            'cidade' => 'São Paulo',
            'estado' => 'SP',
            'cep' => '01000-000',
            'plano' => 'basico',
            'valor_mensalidade' => 199.90,
            'data_vencimento' => now()->addMonth(),
            'ativo' => true,
            'em_dia' => true,
            'configuracoes' => json_encode(['max_usuarios' => 50])
        ]);

        // Gateway ativo para a escola (sem segredos para evitar Crypt em teste)
        $this->gateway = FinanceGateway::create([
            'school_id' => $this->escola->id,
            'alias' => 'fakepay',
            'name' => 'FakePay Gateway',
            'active' => true,
        ]);

        // Plano de cobrança e assinatura (opcional)
        $this->plan = BillingPlan::create([
            'school_id' => $this->escola->id,
            'name' => 'Mensalidade',
            'amount_cents' => 10000,
            'currency' => 'BRL',
            'periodicity' => 'monthly',
            'day_of_month' => 10,
            'grace_days' => 5,
            'penalty_policy' => json_encode(['fine_percent' => 2]),
            'active' => true,
        ]);

        $this->subscription = Subscription::create([
            'school_id' => $this->escola->id,
            'billing_plan_id' => $this->plan->id,
            'status' => 'active',
            'start_at' => now()->subMonth(),
            'end_at' => null,
            'discount_percent' => 0,
            'notes' => null,
        ]);
    }

    public function test_webhook_charge_paid_updates_invoice_and_creates_payment_without_signature(): void
    {
        $invoice = Invoice::create([
            'school_id' => $this->escola->id,
            'subscription_id' => $this->subscription?->id,
            'number' => 'FAT-1001',
            'due_date' => now()->addDays(7),
            'total_cents' => 10000,
            'currency' => 'BRL',
            'status' => 'pending',
        ]);

        $payload = [
            'event' => 'charge_paid',
            'invoice_id' => $invoice->id,
            'charge_id' => 'ch_123',
            'payment_id' => 'pay_456',
            'amount' => 100.00,
            'method' => 'pix',
            'paid_at' => now()->toISOString(),
        ];

        $resp = $this->postJson('/api/v1/webhooks/gateway/' . $this->gateway->alias, $payload);
        $resp->assertStatus(200)->assertJson(['status' => 'processed']);

        $invoice->refresh();
        $this->assertSame('paid', $invoice->status);
        $this->assertSame('ch_123', $invoice->charge_id);
        $this->assertSame('fakepay', $invoice->gateway_alias);

        $payment = Payment::where('invoice_id', $invoice->id)->first();
        $this->assertNotNull($payment, 'Pagamento deve existir');
        $this->assertSame(10000, $payment->amount_paid_cents);
        $this->assertSame('confirmed', $payment->status);
        $this->assertSame('pix', $payment->method);
        $this->assertSame('pay_456', $payment->gateway_payment_id);
    }

    public function test_webhook_charge_created_updates_invoice_fields_without_signature(): void
    {
        $invoice = Invoice::create([
            'school_id' => $this->escola->id,
            'subscription_id' => $this->subscription?->id,
            'number' => 'FAT-1002',
            'due_date' => now()->addDays(10),
            'total_cents' => 15000,
            'currency' => 'BRL',
            'status' => 'pending',
        ]);

        $payload = [
            'event' => 'charge_created',
            'invoice_id' => $invoice->id,
            'charge_id' => 'ch_789',
            'boleto_url' => 'https://boleto.example/xyz',
            'barcode' => '1234567890',
            'linha_digitavel' => '00190.00009 01234.567890 12345.678903 1 23450000010000',
        ];

        $resp = $this->postJson('/api/v1/webhooks/gateway/' . $this->gateway->alias, $payload);
        $resp->assertStatus(200)->assertJson(['status' => 'processed']);

        $invoice->refresh();
        $this->assertSame('pending', $invoice->status);
        $this->assertSame('ch_789', $invoice->charge_id);
        $this->assertSame('https://boleto.example/xyz', $invoice->boleto_url);
        $this->assertSame('1234567890', $invoice->barcode);
        $this->assertSame('00190.00009 01234.567890 12345.678903 1 23450000010000', $invoice->linha_digitavel);
    }

    public function test_webhook_without_invoice_is_accepted_not_processed(): void
    {
        // Fatura existente para garantir que nada seja alterado
        $invoice = Invoice::create([
            'school_id' => $this->escola->id,
            'subscription_id' => $this->subscription?->id,
            'number' => 'FAT-2001',
            'due_date' => now()->addDays(5),
            'total_cents' => 20000,
            'currency' => 'BRL',
            'status' => 'pending',
        ]);

        $payload = [
            'event' => 'charge_paid',
            'invoice_id' => $invoice->id + 999, // inexistente
            'charge_id' => 'ch_missing',
            'payment_id' => 'pay_missing',
        ];

        $resp = $this->postJson('/api/v1/webhooks/gateway/' . $this->gateway->alias, $payload);
        $resp->assertStatus(200)->assertJson(['status' => 'accepted']);

        // Garantir que a fatura original não foi alterada
        $invoice->refresh();
        $this->assertSame('pending', $invoice->status);
        $this->assertNull(Payment::where('invoice_id', $invoice->id)->first());
    }

    public function test_webhook_charge_canceled_updates_invoice_status_without_signature(): void
    {
        $invoice = Invoice::create([
            'school_id' => $this->escola->id,
            'subscription_id' => $this->subscription?->id,
            'number' => 'FAT-3001',
            'due_date' => now()->addDays(3),
            'total_cents' => 12000,
            'currency' => 'BRL',
            'status' => 'pending',
        ]);

        $payload = [
            'event' => 'charge_canceled',
            'invoice_id' => $invoice->id,
            'charge_id' => 'ch_cancel_001',
        ];

        $resp = $this->postJson('/api/v1/webhooks/gateway/' . $this->gateway->alias, $payload);
        $resp->assertStatus(200)->assertJson(['status' => 'processed']);

        $invoice->refresh();
        $this->assertSame('canceled', $invoice->status);
        $this->assertSame('ch_cancel_001', $invoice->charge_id);
        $this->assertSame('fakepay', $invoice->gateway_alias);

        $payment = Payment::where('invoice_id', $invoice->id)->first();
        $this->assertNull($payment, 'Cancelamento não deve criar pagamento');
    }

    public function test_webhook_payment_refunded_marks_existing_payment_refunded_without_signature(): void
    {
        $invoice = Invoice::create([
            'school_id' => $this->escola->id,
            'subscription_id' => $this->subscription?->id,
            'number' => 'FAT-3002',
            'due_date' => now()->addDays(8),
            'total_cents' => 18000,
            'currency' => 'BRL',
            'status' => 'paid',
        ]);

        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'amount_paid_cents' => 18000,
            'paid_at' => now()->subDay(),
            'method' => 'pix',
            'currency' => 'BRL',
            'gateway_payment_id' => 'pay_refund_001',
            'status' => 'confirmed',
        ]);

        $payload = [
            'event' => 'payment_refunded',
            'invoice_id' => $invoice->id,
            'payment_id' => 'pay_refund_001',
            'refund_amount' => 50.00,
            'refunded_at' => now()->toISOString(),
            'method' => 'pix',
        ];

        $resp = $this->postJson('/api/v1/webhooks/gateway/' . $this->gateway->alias, $payload);
        $resp->assertStatus(200)->assertJson(['status' => 'processed']);

        $payment->refresh();
        $this->assertSame('refunded', $payment->status);
        $this->assertSame('pay_refund_001', $payment->gateway_payment_id);
    }

    public function test_webhook_charge_refunded_creates_refund_payment_without_existing_payment(): void
    {
        $invoice = Invoice::create([
            'school_id' => $this->escola->id,
            'subscription_id' => $this->subscription?->id,
            'number' => 'FAT-4001',
            'due_date' => now()->addDays(6),
            'total_cents' => 15000,
            'currency' => 'BRL',
            'status' => 'paid',
        ]);

        $payload = [
            'event' => 'charge_refunded',
            'invoice_id' => $invoice->id,
            'refund_amount' => 75.00,
            'refunded_at' => now()->toISOString(),
            'method' => 'pix',
        ];

        $resp = $this->postJson('/api/v1/webhooks/gateway/' . $this->gateway->alias, $payload);
        $resp->assertStatus(200)->assertJson(['status' => 'processed']);

        $payment = Payment::where('invoice_id', $invoice->id)
                          ->where('status', 'refunded')
                          ->first();

        $this->assertNotNull($payment, 'Pagamento de reembolso deve ser criado');
        $this->assertSame(7500, $payment->amount_paid_cents);
        $this->assertSame('pix', $payment->method);
    }

    public function test_webhook_invalid_signature_is_accepted_and_no_changes(): void
    {
        $invoice = Invoice::create([
            'school_id' => $this->escola->id,
            'subscription_id' => $this->subscription?->id,
            'number' => 'FAT-4002',
            'due_date' => now()->addDays(4),
            'total_cents' => 9999,
            'currency' => 'BRL',
            'status' => 'pending',
        ]);

        // Definir um segredo de webhook e enviar assinatura inválida
        $this->gateway->webhook_secret = 'abc123';
        $this->gateway->save();

        $payload = [
            'event' => 'charge_paid',
            'invoice_id' => $invoice->id,
            'charge_id' => 'ch_sig',
            'payment_id' => 'pay_sig',
            'amount' => 100.00,
            'paid_at' => now()->toISOString(),
        ];

        $resp = $this->withHeaders(['X-Signature' => 'sha256=deadbeef'])
                    ->postJson('/api/v1/webhooks/gateway/' . $this->gateway->alias, $payload);

        $resp->assertStatus(200)->assertJson(['status' => 'accepted']);

        // Sem alterações na fatura e nenhum pagamento criado
        $invoice->refresh();
        $this->assertSame('pending', $invoice->status);
        $this->assertNull(Payment::where('invoice_id', $invoice->id)->first());
    }
}