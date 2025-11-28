<?php

namespace App\Http\Controllers\Finance;

use App\Models\Finance\Invoice;
use App\Models\Finance\Payment;
use App\Models\Finance\FinanceGateway;
use App\Models\Finance\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function receive(Request $request, string $alias)
    {
        // Basic headers commonly used by gateways
        $signature = $request->header('X-Signature') ?? $request->header('X-Hub-Signature') ?? null;
        $eventType = $request->header('X-Event-Type') ?? $request->input('event') ?? $request->input('type') ?? null;
        $externalId = $request->input('id') ?? $request->input('event_id') ?? null;

        $payload = $request->getContent();
        $json = [];
        try {
            $json = json_decode($payload, true) ?: [];
        } catch (\Throwable $e) {
            // manter $json vazio
        }
        // Em ambientes de teste, o corpo pode estar vazio enquanto os inputs estão disponíveis
        // Garanta que os dados usados para processamento incluam todos os parâmetros recebidos
        $data = !empty($json) ? $json : $request->all();

        $event = new WebhookEvent();
        $event->gateway_alias = $alias;
        $event->event_type = $eventType;
        $event->external_id = $externalId;
        $event->signature_valid = null; // Validado posteriormente com o segredo do gateway
        $event->payload = $payload;
        $event->processed = false;
        $event->attempts = 0;
        $event->save();

        // Tentar processar imediatamente
        try {
            $this->processEvent($event, $alias, $eventType, $signature, $data);
        } catch (\Throwable $e) {
            $event->last_error = $e->getMessage();
            $event->attempts = ($event->attempts ?? 0) + 1;
            $event->save();
            Log::error('Webhook processing failed', [
                'alias' => $alias,
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Retornar rapidamente 200 para evitar retries desnecessários
        return response()->json(['status' => $event->processed ? 'processed' : 'accepted', 'event_id' => $event->id]);
    }

    protected function processEvent(WebhookEvent $event, string $alias, ?string $eventType, ?string $signature, array $data): void
    {
        // Normalizar payload específico do gateway para chaves internas
        $data = $this->normalizeGatewayPayload($alias, $data);
        // Tentar localizar fatura alvo por diferentes identificadores
        $invoice = $this->findInvoiceForEvent($data);

        // Se não achou a fatura, apenas manter aceito para evitar retry incessante
        if (!$invoice) {
            Log::warning('Webhook without resolvable invoice', ['alias' => $alias, 'event' => $event->id]);
            return; // mantém processed = false
        }

        // Validar ambiente do gateway antes de processar
        $gateway = FinanceGateway::where('school_id', $invoice->school_id)
            ->where('alias', $alias)
            ->where('active', true)
            ->first();
        if ($gateway) {
            $currentFinanceEnv = config('features.finance_env', 'production');
            $gwEnv = $gateway->environment ?? 'production';
            if ($gwEnv !== $currentFinanceEnv) {
                Log::info('Webhook ignored due to environment mismatch', [
                    'invoice_id' => $invoice->id,
                    'gateway_env' => $gwEnv,
                    'current_env' => $currentFinanceEnv,
                ]);
                return; // mantém processed = false
            }
        }

        // Validar assinatura usando o segredo do gateway por escola
        if ($gateway && $signature) {
            $valid = $this->verifySignature($event->payload, $signature, $gateway->webhook_secret);
            $event->signature_valid = $valid;
        } else {
            // Sem gateway/assinatura, manter null
            $event->signature_valid = $event->signature_valid; // no-op
        }
        $event->save();

        // Se assinatura estiver presente e inválida, não processar
        if ($signature && $gateway && $event->signature_valid === false) {
            Log::warning('Invalid webhook signature', ['alias' => $alias, 'event' => $event->id]);
            return;
        }

        // Normalizar tipo de evento
        $type = $this->normalizeEventType($eventType, $data);

        // Aplicar evento na fatura/pagamento
        $this->applyEvent($invoice, $type, $alias, $data);

        $event->processed = true;
        $event->attempts = ($event->attempts ?? 0) + 1;
        $event->save();
    }

    protected function findInvoiceForEvent(array $data): ?Invoice
    {
        // Prioridades: invoice_id -> charge_id -> number -> payment->invoice_id
        if (!empty($data['invoice_id'])) {
            $inv = Invoice::find((int) $data['invoice_id']);
            if ($inv) return $inv;
        }
        if (!empty($data['charge_id'])) {
            $inv = Invoice::where('charge_id', (string) $data['charge_id'])->first();
            if ($inv) return $inv;
        }
        if (!empty($data['invoice_number'])) {
            $inv = Invoice::where('number', (string) $data['invoice_number'])->first();
            if ($inv) return $inv;
        }
        if (!empty($data['payment_id'])) {
            $pay = Payment::where('gateway_payment_id', (string) $data['payment_id'])->first();
            if ($pay) {
                $inv = Invoice::find($pay->invoice_id);
                if ($inv) return $inv;
            }
        }
        return null;
    }

    protected function normalizeGatewayPayload(string $alias, array $data): array
    {
        $alias = strtolower((string) $alias);
        if ($alias === 'assas') { $alias = 'asaas'; }
        if ($alias === 'asaas') {
            // Flatten ASAAS payload fields
            if (isset($data['payment'])) {
                $p = $data['payment'];
                if (is_array($p)) {
                    $data['payment_id'] = $data['payment_id'] ?? ($p['id'] ?? null);
                    // Map charge_id to payment_id if not present
                    $data['charge_id'] = $data['charge_id'] ?? ($data['payment_id'] ?? null);
                    $data['invoice_number'] = $data['invoice_number'] ?? ($p['invoiceNumber'] ?? null);
                    // Status mapping
                    $data['status'] = strtolower((string)($p['status'] ?? $data['status'] ?? ''));
                    // Payment method
                    $bt = strtoupper((string)($p['billingType'] ?? ''));
                    $data['payment_method'] = $data['payment_method'] ?? (
                        $bt === 'PIX' ? 'pix' : ($bt === 'BOLETO' ? 'boleto' : ($bt === 'CREDIT_CARD' ? 'card' : null))
                    );
                    // PIX/Boleto fields
                    if (!empty($p['pixCode'])) $data['pix_code'] = $p['pixCode'];
                    if (!empty($p['pixQrCode'])) $data['pix_qr_code'] = $p['pixQrCode'];
                    if (!empty($p['bankSlipUrl'])) $data['boleto_url'] = $p['bankSlipUrl'];
                    if (!empty($p['digitableLine'])) $data['linha_digitavel'] = $p['digitableLine'];
                    // Amounts and dates
                    if (isset($p['netValue'])) {
                        $data['amount_paid'] = $data['amount_paid'] ?? (float)$p['netValue'];
                        $data['fee_cents'] = isset($p['fee']) ? (int) round(((float)$p['fee']) * 100) : ($data['fee_cents'] ?? null);
                    } elseif (isset($p['value'])) {
                        $data['amount_paid'] = $data['amount_paid'] ?? (float)$p['value'];
                    }
                    $paidAt = $p['confirmedDate'] ?? $p['clientPaymentDate'] ?? $p['paymentDate'] ?? null;
                    if ($paidAt && empty($data['paid_at'])) $data['paid_at'] = $paidAt;
                } else {
                    $pid = (string)$p;
                    if ($pid) {
                        $data['payment_id'] = $data['payment_id'] ?? $pid;
                        $data['charge_id'] = $data['charge_id'] ?? $pid;
                    }
                }
            }
            // Some webhooks send 'id' as payment id directly
            if (!empty($data['id']) && empty($data['payment_id'])) {
                $data['payment_id'] = (string)$data['id'];
                $data['charge_id'] = $data['charge_id'] ?? (string)$data['id'];
            }
        } elseif ($alias === 'nupay') {
            // Normalização mínima para NuPay
            if (!empty($data['transactionId']) && empty($data['payment_id'])) {
                $data['payment_id'] = (string)$data['transactionId'];
                $data['charge_id'] = $data['charge_id'] ?? (string)$data['transactionId'];
            }
            if (!empty($data['referenceId']) && empty($data['payment_id'])) {
                $data['payment_id'] = (string)$data['referenceId'];
                $data['charge_id'] = $data['charge_id'] ?? (string)$data['referenceId'];
            }
            if (isset($data['amount']) && is_array($data['amount'])) {
                if (isset($data['amount']['value'])) $data['amount_paid'] = (float)$data['amount']['value'];
                if (isset($data['amount']['fee'])) $data['fee_cents'] = (int) round(((float)$data['amount']['fee']) * 100);
            }
            if (!empty($data['status'])) $data['status'] = strtolower((string)$data['status']);
            if (!empty($data['paymentMethod']['type'])) {
                $t = strtolower((string)$data['paymentMethod']['type']);
                $data['payment_method'] = $t;
            }
        }
        return $data;
    }

    protected function verifySignature(string $payload, string $signature, ?string $secret): bool
    {
        if (!$secret) return false;
        // Aceitar formatos: "sha256=<hex>" ou apenas "<hex>"
        $sig = $signature;
        if (str_contains($sig, '=')) {
            [$algo, $hash] = explode('=', $sig, 2);
            $sig = $hash;
        }
        $calc = hash_hmac('sha256', $payload, $secret);
        return hash_equals($calc, $sig);
    }

    protected function normalizeEventType(?string $raw, array $data): string
    {
        $raw = $raw ?? ($data['event'] ?? $data['type'] ?? '');
        $raw = strtolower((string)$raw);
        // Converter formatos com ponto (ex.: "payment.confirmed") para underscore
        if ($raw) {
            $raw = str_replace('.', '_', $raw);
        }
        if (!$raw && isset($data['status'])) {
            $st = strtolower((string)$data['status']);
            return match ($st) {
                'paid', 'confirmed' => 'charge_paid',
                'canceled' => 'charge_canceled',
                'refunded' => 'charge_refunded',
                default => 'charge_updated',
            };
        }
        return $raw ?: 'charge_updated';
    }

    protected function applyEvent(Invoice $invoice, string $type, string $alias, array $data): void
    {
        // Atualiza dados comuns da fatura se disponíveis
        $updatedFields = [];
        if (isset($data['charge_id'])) $updatedFields['charge_id'] = (string)$data['charge_id'];
        if (isset($data['boleto_url'])) $updatedFields['boleto_url'] = (string)$data['boleto_url'];
        if (isset($data['barcode'])) $updatedFields['barcode'] = (string)$data['barcode'];
        if (isset($data['linha_digitavel'])) $updatedFields['linha_digitavel'] = (string)$data['linha_digitavel'];
        if (isset($data['pix_qr_code'])) $updatedFields['pix_qr_code'] = (string)$data['pix_qr_code'];
        if (isset($data['pix_code'])) $updatedFields['pix_code'] = (string)$data['pix_code'];
        if (!empty($updatedFields)) {
            $invoice->fill($updatedFields);
        }
        $invoice->gateway_alias = $invoice->gateway_alias ?: $alias;

        switch ($type) {
            case 'charge_created':
            case 'boleto_generated':
            case 'invoice_created':
            case 'charge_updated':
                // Apenas atualizar dados, manter status como está ou pending
                if (!$invoice->status) $invoice->status = 'pending';
                $invoice->save();
                Log::info('Invoice updated via webhook', ['invoice_id' => $invoice->id, 'type' => $type]);
                break;

            case 'charge_paid':
            case 'invoice_paid':
            case 'payment_confirmed':
                $invoice->status = 'paid';
                if (!$invoice->paid_at) {
                    $paidAt = null;
                    if (!empty($data['paid_at'])) {
                        try { $paidAt = \Carbon\Carbon::parse($data['paid_at']); } catch (\Throwable $e) { $paidAt = null; }
                    }
                    $invoice->paid_at = $paidAt ?: now();
                }
                $invoice->save();
                $this->ensurePaymentRecord($invoice, $data, 'confirmed');
                Log::info('Invoice marked paid via webhook', ['invoice_id' => $invoice->id, 'type' => $type]);
                break;

            case 'charge_canceled':
            case 'invoice_canceled':
                $invoice->status = 'canceled';
                $invoice->save();
                Log::info('Invoice canceled via webhook', ['invoice_id' => $invoice->id, 'type' => $type]);
                break;

            case 'charge_refunded':
            case 'payment_refunded':
                $this->markPaymentRefunded($invoice, $data);
                Log::info('Payment refunded via webhook', ['invoice_id' => $invoice->id, 'type' => $type]);
                break;

            default:
                // Evento desconhecido, apenas persistir alterações básicas
                $invoice->save();
                Log::info('Unhandled webhook event type', ['invoice_id' => $invoice->id, 'type' => $type]);
                break;
        }
    }

    protected function ensurePaymentRecord(Invoice $invoice, array $data, string $status): void
    {
        $gatewayPaymentId = isset($data['payment_id']) ? (string)$data['payment_id'] : null;
        $existing = $gatewayPaymentId ? Payment::where('invoice_id', $invoice->id)
            ->where('gateway_payment_id', $gatewayPaymentId)
            ->first() : null;
        if ($existing) {
            // Atualizar status e valores se vierem
            if (isset($data['amount'])) $existing->amount_paid_cents = (int) round(((float)$data['amount']) * 100);
            if (isset($data['paid_at'])) $existing->paid_at = $data['paid_at'];
            if (isset($data['fee'])) {
                $existing->gateway_fee_cents = (int) round(((float)$data['fee']) * 100);
            }
            if ($existing->amount_paid_cents !== null && $existing->gateway_fee_cents !== null) {
                $existing->net_amount_cents = max(0, $existing->amount_paid_cents - $existing->gateway_fee_cents);
            }
            $existing->status = $status;
            $existing->save();
            return;
        }

        $method = $this->inferMethod($data);
        $amountCents = isset($data['amount']) ? (int) round(((float)$data['amount']) * 100) : $invoice->total_cents;
        $feeCents = isset($data['fee']) ? (int) round(((float)$data['fee']) * 100) : null;
        $attrs = [
            'invoice_id' => $invoice->id,
            'amount_paid_cents' => $amountCents,
            'paid_at' => $data['paid_at'] ?? now(),
            'method' => $method,
            'currency' => $invoice->currency ?? 'BRL',
            'gateway_payment_id' => $gatewayPaymentId,
            'status' => $status,
        ];
        if ($feeCents !== null) {
            $attrs['gateway_fee_cents'] = $feeCents;
            $attrs['net_amount_cents'] = max(0, $amountCents - $feeCents);
        }
        $payment = new Payment($attrs);
        $payment->save();
    }

    protected function markPaymentRefunded(Invoice $invoice, array $data): void
    {
        $gatewayPaymentId = isset($data['payment_id']) ? (string)$data['payment_id'] : null;
        if ($gatewayPaymentId) {
            $payment = Payment::where('invoice_id', $invoice->id)
                ->where('gateway_payment_id', $gatewayPaymentId)
                ->first();
            if ($payment) {
                $payment->status = 'refunded';
                $payment->save();
                return;
            }
        }
        // Se não achou, criar um registro simples de estorno
        $refundAmount = isset($data['refund_amount']) ? (int) round(((float)$data['refund_amount']) * 100) : null;
        $payment = new Payment([
            'invoice_id' => $invoice->id,
            'amount_paid_cents' => $refundAmount ?? 0,
            'paid_at' => $data['refunded_at'] ?? now(),
            'method' => $this->inferMethod($data),
            'currency' => $invoice->currency ?? 'BRL',
            'gateway_payment_id' => $gatewayPaymentId,
            'status' => 'refunded',
        ]);
        $payment->save();
    }

    protected function inferMethod(array $data): string
    {
        if (!empty($data['method'])) return (string)$data['method'];
        if (!empty($data['payment_method'])) return (string)$data['payment_method'];
        if (!empty($data['pix_qr_code']) || !empty($data['pix_code'])) return 'pix';
        if (!empty($data['linha_digitavel']) || !empty($data['barcode']) || !empty($data['boleto_url'])) return 'boleto';
        return 'card';
    }
}