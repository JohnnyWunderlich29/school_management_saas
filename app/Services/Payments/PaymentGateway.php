<?php

namespace App\Services\Payments;

use App\Models\Finance\FinanceGateway;

interface PaymentGateway
{
    public function alias(): string;

    /** Create or update a customer in the gateway */
    public function createOrUpdateCustomer(array $context, array $payer): array;

    /** Create a charge for an invoice (boleto/PIX/link) */
    public function createCharge(array $invoiceContext): array;

    /** Cancel a charge */
    public function cancelCharge(string $chargeId): bool;

    /** Refund a payment */
    public function refundPayment(string $paymentId, ?int $amountCents = null): bool;

    /** Validate and parse webhook payload into normalized event */
    public function parseWebhook(string $payload, ?string $signature): array;

    /** Get payment details by external gateway id */
    public function getPayment(string $paymentId): array;

    /** Optional: obtain settlement details (fees, net, dates) */
    public function getSettlementDetails(string $paymentId): array;

    /** Configure instance from FinanceGateway model */
    public function configure(FinanceGateway $gateway): void;
}