<?php

namespace App\Services\Payments;

use App\Models\Finance\FinanceGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NuPayGateway implements PaymentGateway
{
    protected string $baseUrl = 'https://api.nupaybusiness.com.br/checkout';
    protected ?string $apiKey = null;
    protected ?string $environment = null;

    public function alias(): string
    {
        return 'nupay';
    }

    public function configure(FinanceGateway $gateway): void
    {
        $this->environment = $gateway->environment ?: 'production';
        // Permitir override via credenciais para evitar acoplamento com URLs
        $creds = $gateway->credentials ?? [];
        $this->baseUrl = $creds['base_url'] ?? (
            in_array($this->environment, ['sandbox', 'homolog'])
                ? 'https://sandbox.nupaybusiness.com.br/checkout'
                : 'https://api.nupaybusiness.com.br/checkout'
        );
        // Header Authorization pode ser necessário para fluxos pré-autorizados
        $this->apiKey = $creds['api_key'] ?? $creds['Authorization'] ?? $creds['access_token'] ?? null;
    }

    protected function headers(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
        if ($this->apiKey) {
            $headers['Authorization'] = $this->apiKey; // conforme docs: usado para pré-autorização
        }
        return $headers;
    }

    /**
     * NuPay não requer cadastro de cliente separado; retornamos no-op.
     */
    public function createOrUpdateCustomer(array $context, array $payer): array
    {
        return ['id' => $payer['external_id'] ?? null, 'raw' => $payer];
    }

    /**
     * Cria um pedido de pagamento NuPay.
     * Espera invoiceContext com: amount_cents, invoice_id/invoice_number, description,
     * optional return_url, order_url
     */
    public function createCharge(array $invoiceContext): array
    {
        $amount = isset($invoiceContext['amount_cents'])
            ? ((float)$invoiceContext['amount_cents']) / 100.0
            : (float)($invoiceContext['value'] ?? 0);

        $merchantRef = (string)($invoiceContext['invoice_id'] ?? $invoiceContext['invoice_number'] ?? '');
        $callbackUrl = url('/api/v1/webhooks/gateway/nupay');
        $returnUrl = $invoiceContext['return_url'] ?? null;
        $orderUrl = $invoiceContext['order_url'] ?? null;

        // Mapear dados do pagador para shopper
        $payer = $invoiceContext['payer'] ?? [];
        $shopper = [
            'firstName' => $payer['firstName'] ?? $payer['name'] ?? null,
            'lastName' => $payer['lastName'] ?? null,
            'document' => $payer['cpf'] ?? $payer['document'] ?? null,
            'documentType' => $payer['documentType'] ?? 'CPF',
            'email' => $payer['email'] ?? null,
            'phone' => [
                'country' => '55',
                'number' => $payer['mobilePhone'] ?? $payer['phone'] ?? null,
            ],
        ];

        $payload = [
            'merchantOrderReference' => $merchantRef,
            'amount' => [
                'value' => $amount,
                'currency' => 'BRL',
            ],
            'paymentMethod' => [
                'type' => 'nupay',
                // authorizationType pode ser "manually_authorized" ou "auto_authorized" conforme docs
                'authorizationType' => $invoiceContext['authorization_type'] ?? 'manually_authorized',
            ],
            'shopper' => $shopper,
            'callbackUrl' => $callbackUrl,
        ];
        if ($returnUrl) $payload['returnUrl'] = $returnUrl;
        if ($orderUrl) $payload['orderUrl'] = $orderUrl;
        if (!empty($invoiceContext['description'])) $payload['description'] = $invoiceContext['description'];

        $headers = $this->headers();
        try {
            $resp = Http::withHeaders($headers)
                ->post(rtrim($this->baseUrl, '/') . '/payments', $payload);
            $data = $resp->json();
            if ($resp->successful()) {
                return [
                    'charge_id' => $data['transactionId'] ?? $data['referenceId'] ?? null,
                    'payment_url' => $data['paymentUrl'] ?? null,
                    'raw' => $data,
                ];
            }
            $status = $resp->status();
            Log::warning('NuPay create charge failed', ['status' => $status, 'error' => $data]);
            return ['error' => $data ?: ['message' => 'create charge failed'], 'status' => $status];
        } catch (\Throwable $e) {
            Log::error('NuPay charge exception', ['message' => $e->getMessage()]);
            return ['error' => ['message' => $e->getMessage()]];
        }
    }

    public function cancelCharge(string $chargeId): bool
    {
        $headers = $this->headers();
        try {
            $resp = Http::withHeaders($headers)
                ->post(rtrim($this->baseUrl, '/') . '/payments/' . urlencode($chargeId) . '/cancel');
            return $resp->successful();
        } catch (\Throwable $e) {
            Log::error('NuPay cancel exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function refundPayment(string $paymentId, ?int $amountCents = null): bool
    {
        $headers = $this->headers();
        $payload = [];
        if ($amountCents !== null) {
            $payload['value'] = ((float)$amountCents) / 100.0;
        }
        try {
            $resp = Http::withHeaders($headers)
                ->post(rtrim($this->baseUrl, '/') . '/payments/' . urlencode($paymentId) . '/refund', $payload);
            return $resp->successful();
        } catch (\Throwable $e) {
            Log::error('NuPay refund exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function parseWebhook(string $payload, ?string $signature): array
    {
        $data = json_decode($payload, true) ?: [];
        $type = $data['event'] ?? $data['type'] ?? 'unknown';
        // NuPay retorna transactionId/referenceId
        $paymentId = $data['transactionId'] ?? $data['referenceId'] ?? null;
        $status = $data['status'] ?? $data['paymentStatus'] ?? null;
        $amount = null;
        if (isset($data['amount']) && is_array($data['amount']) && isset($data['amount']['value'])) {
            $amount = (float)$data['amount']['value'];
        }
        return [
            'type' => $type,
            'payment_id' => $paymentId,
            'status' => $status,
            'amount' => $amount,
            'raw' => $data,
        ];
    }

    public function getPayment(string $paymentId): array
    {
        $headers = $this->headers();
        try {
            $resp = Http::withHeaders($headers)
                ->get(rtrim($this->baseUrl, '/') . '/payments/' . urlencode($paymentId));
            $data = $resp->json();
            if ($resp->successful()) {
                $valueCents = null;
                if (isset($data['amount']['value'])) {
                    $valueCents = (int) round(((float)$data['amount']['value']) * 100);
                }
                $status = $data['status'] ?? $data['paymentStatus'] ?? null;
                return [
                    'id' => $data['transactionId'] ?? $data['referenceId'] ?? $paymentId,
                    'status' => $status,
                    'value_cents' => $valueCents,
                    'paid_at' => $data['paidAt'] ?? $data['paymentDate'] ?? null,
                    'payment_url' => $data['paymentUrl'] ?? null,
                    'raw' => $data,
                ];
            }
            return ['error' => $data, 'status_code' => $resp->status()];
        } catch (\Throwable $e) {
            Log::error('NuPay getPayment error', ['paymentId' => $paymentId, 'message' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function getSettlementDetails(string $paymentId): array
    {
        // Não implementado; dependerá de API de conciliação
        return [];
    }
}