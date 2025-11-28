<?php

namespace App\Services\Payments;

use App\Models\Finance\FinanceGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AsaasGateway implements PaymentGateway
{
    protected string $baseUrl = 'https://api.asaas.com/api/v3';
    protected ?string $apiKey = null;
    protected ?string $environment = null;

    public function alias(): string
    {
        return 'asaas';
    }

    public function configure(FinanceGateway $gateway): void
    {
        $this->environment = $gateway->environment ?: 'production';
        $this->baseUrl = in_array($this->environment, ['sandbox', 'homolog'])
            ? 'https://sandbox.asaas.com/api/v3'
            : 'https://api.asaas.com/api/v3';
        $creds = $gateway->credentials ?? [];
        $this->apiKey = $creds['api_key'] ?? $creds['access_token'] ?? $creds['Authorization'] ?? null;
    }

    protected function headers(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            // Asaas supports either `access_token` or `Authorization` with the API key directly
            'access_token' => $this->apiKey,
            'Authorization' => $this->apiKey,
        ];
    }

    public function createOrUpdateCustomer(array $context, array $payer): array
    {
        $payload = [
            'name' => $payer['name'] ?? null,
            'cpfCnpj' => $payer['cpfCnpj'] ?? ($payer['cpf'] ?? $payer['cnpj'] ?? null),
            'email' => $payer['email'] ?? null,
            'phone' => $payer['phone'] ?? null,
            'mobilePhone' => $payer['mobilePhone'] ?? null,
            'address' => $payer['address'] ?? null,
            'postalCode' => $payer['postalCode'] ?? null,
            'city' => $payer['city'] ?? null,
            'state' => $payer['state'] ?? null,
            'observations' => $payer['observations'] ?? null,
            'externalReference' => $payer['externalReference'] ?? null,
        ];

        $headers = $this->headers();
        $externalId = $payer['external_id'] ?? null; // previously saved gateway customer id

        try {
            if ($externalId) {
                $resp = Http::withHeaders($headers)
                    ->put(rtrim($this->baseUrl, '/') . '/customers/' . urlencode($externalId), $payload);
                if ($resp->successful()) {
                    $data = $resp->json();
                    return ['id' => $data['id'] ?? $externalId, 'raw' => $data];
                }
                // Fallback to create if update failed (e.g., not found)
            }

            $resp = Http::withHeaders($headers)
                ->post(rtrim($this->baseUrl, '/') . '/customers', $payload);
            if ($resp->successful()) {
                $data = $resp->json();
                return ['id' => $data['id'] ?? null, 'raw' => $data];
            }

            $status = $resp->status();
            Log::warning('Asaas create customer failed', ['status' => $status, 'error' => $resp->json()]);
            return ['id' => null, 'error' => $resp->json() ?: ['message' => 'create customer failed'], 'status' => $status];
        } catch (\Throwable $e) {
            Log::error('Asaas customer exception', ['message' => $e->getMessage()]);
            return ['id' => null, 'error' => ['message' => $e->getMessage()]];
        }
    }

    public function createCharge(array $invoiceContext): array
    {
        $billingType = strtoupper($invoiceContext['billingType'] ?? $invoiceContext['method'] ?? 'BOLETO');
        if (!in_array($billingType, ['BOLETO', 'PIX', 'CREDIT_CARD', 'DEBIT_CARD'])) {
            $billingType = 'BOLETO';
        }
        $value = isset($invoiceContext['amount_cents']) ? ((float)$invoiceContext['amount_cents'] / 100.0) : (float)($invoiceContext['value'] ?? 0);
        $payload = [
            'customer' => $invoiceContext['customer_id'] ?? $invoiceContext['customer'] ?? null,
            'billingType' => $billingType,
            'value' => $value,
            'description' => $invoiceContext['description'] ?? null,
            'dueDate' => $invoiceContext['due_date'] ?? null,
            'externalReference' => (string)($invoiceContext['invoice_id'] ?? $invoiceContext['invoice_number'] ?? ''),
        ];

        $headers = $this->headers();
        try {
            $resp = Http::withHeaders($headers)
                ->post(rtrim($this->baseUrl, '/') . '/payments', $payload);
            $data = $resp->json();
            if ($resp->successful()) {
                $chargeId = $data['id'] ?? null;
                $boletoUrl = $data['bankSlipUrl'] ?? ($data['invoiceUrl'] ?? null);
                $linhaDigitavel = $data['digitableLine'] ?? null;
                $barcode = $data['barcode'] ?? null;
                $pixCode = $data['pixCopyPaste'] ?? $data['pixKey'] ?? null;
                $pixQrCode = $data['pixQrCodeBase64'] ?? $data['pixQrCode'] ?? null;
                return [
                    'charge_id' => $chargeId,
                    'boleto_url' => $boletoUrl,
                    'linha_digitavel' => $linhaDigitavel,
                    'barcode' => $barcode,
                    'pix_code' => $pixCode,
                    'pix_qr_code' => $pixQrCode,
                    'raw' => $data,
                ];
            }
            $status = $resp->status();
            Log::warning('Asaas create charge failed', ['status' => $status, 'error' => $data]);
            return ['error' => $data ?: ['message' => 'create charge failed'], 'status' => $status];
        } catch (\Throwable $e) {
            Log::error('Asaas charge exception', ['message' => $e->getMessage()]);
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
            Log::error('Asaas cancel exception', ['message' => $e->getMessage()]);
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
            Log::error('Asaas refund exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function parseWebhook(string $payload, ?string $signature): array
    {
        // Minimal passthrough; real signature validation can be added using webhook_secret if configured
        $data = json_decode($payload, true) ?: [];
        $type = $data['event'] ?? $data['type'] ?? 'unknown';
        $paymentId = $data['payment'] ?? $data['payment_id'] ?? null;
        $status = $data['status'] ?? null;
        return [
            'type' => $type,
            'payment_id' => $paymentId,
            'status' => $status,
            'raw' => $data,
        ];
    }

    public function getPayment(string $paymentId): array
    {
        $headers = $this->headers();
        try {
            $resp = Http::withHeaders($headers)->get(rtrim($this->baseUrl, '/') . '/payments/' . urlencode($paymentId));
            $data = $resp->json();
            if ($resp->successful()) {
                $valueCents = isset($data['value']) ? (int)round(((float)$data['value']) * 100) : null;
                $netCents = isset($data['netValue']) ? (int)round(((float)$data['netValue']) * 100) : null;
                $feeCents = ($valueCents !== null && $netCents !== null) ? max(0, $valueCents - $netCents) : null;
                return [
                    'id' => $data['id'] ?? $paymentId,
                    'status' => $data['status'] ?? null,
                    'billingType' => $data['billingType'] ?? null,
                    'value_cents' => $valueCents,
                    'net_cents' => $netCents,
                    'fee_cents' => $feeCents,
                    'paid_at' => $data['clientPaymentDate'] ?? $data['confirmedDate'] ?? $data['paymentDate'] ?? null,
                    'boleto_url' => $data['bankSlipUrl'] ?? ($data['invoiceUrl'] ?? null),
                    'digitableLine' => $data['digitableLine'] ?? null,
                    'barcode' => $data['barcode'] ?? null,
                    'pix_code' => $data['pixCopyPaste'] ?? ($data['pixKey'] ?? null),
                    'pix_qr_code' => $data['pixQrCodeBase64'] ?? ($data['pixQrCode'] ?? null),
                    'raw' => $data,
                ];
            }
            return ['error' => $data, 'status_code' => $resp->status()];
        } catch (\Throwable $e) {
            Log::error('ASAAS getPayment error', ['paymentId' => $paymentId, 'message' => $e->getMessage()]);
            return ['error' => $e->getMessage()];
        }
    }

    public function getSettlementDetails(string $paymentId): array
    {
        // Not implemented for now
        return [];
    }

    public function findPaymentByExternalReference(string $externalReference): ?array
    {
        $headers = $this->headers();
        try {
            $url = rtrim($this->baseUrl, '/') . '/payments?externalReference=' . urlencode($externalReference) . '&limit=1';
            $resp = \Illuminate\Support\Facades\Http::withHeaders($headers)->get($url);
            $data = $resp->json();
            if ($resp->successful() && is_array($data) && isset($data['data']) && is_array($data['data']) && count($data['data']) > 0) {
                $p = $data['data'][0];
                return [
                    'id' => $p['id'] ?? null,
                    'status' => $p['status'] ?? null,
                    'billingType' => $p['billingType'] ?? null,
                    'value_cents' => isset($p['value']) ? (int)round(((float)$p['value']) * 100) : null,
                    'boleto_url' => $p['bankSlipUrl'] ?? ($p['invoiceUrl'] ?? null),
                    'digitableLine' => $p['digitableLine'] ?? null,
                    'barcode' => $p['barcode'] ?? null,
                    'pix_code' => $p['pixCopyPaste'] ?? ($p['pixKey'] ?? null),
                    'pix_qr_code' => $p['pixQrCodeBase64'] ?? ($p['pixQrCode'] ?? null),
                    'raw' => $p,
                ];
            }
            return null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Asaas search by externalReference failed', ['message' => $e->getMessage(), 'externalReference' => $externalReference]);
            return null;
        }
    }
}