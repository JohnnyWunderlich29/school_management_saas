<?php

namespace App\Services\Payments;

use App\Models\Finance\FinanceGateway;
use InvalidArgumentException;

class GatewayManager
{
    /** @var array<string, PaymentGateway> */
    protected array $gateways = [];

    public function register(PaymentGateway $gateway): void
    {
        $this->gateways[$gateway->alias()] = $gateway;
    }

    public function has(string $alias): bool
    {
        return isset($this->gateways[$alias]);
    }

    public function forAlias(string $alias, FinanceGateway $config): PaymentGateway
    {
        if (!$this->has($alias)) {
            throw new InvalidArgumentException("Gateway '{$alias}' não registrado");
        }
        $gw = $this->gateways[$alias];
        $gw->configure($config);
        return $gw;
    }
}