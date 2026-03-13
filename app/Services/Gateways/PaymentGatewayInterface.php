<?php

namespace App\Services\Gateways;

interface PaymentGatewayInterface
{
    public function charge(array $paymentData): array;

    public function refund(string $transactionId): array;
}
