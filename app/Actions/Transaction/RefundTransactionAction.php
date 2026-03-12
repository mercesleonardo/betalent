<?php

namespace App\Actions\Transaction;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\Gateways\PaymentGatewayInterface;
use Exception;

class RefundTransactionAction
{
    public function execute(Transaction $transaction): Transaction
    {
        if ($transaction->status !== TransactionStatus::PAID) {
            throw new Exception('Apenas transações com estado PAID podem ser reembolsadas.');
        }

        $transaction->loadMissing('gateway');

        if (!$transaction->gateway || !$transaction->external_id) {
            throw new Exception('Dados do gateway em falta. Não é possível processar o reembolso.');
        }

        $gatewayService = $this->resolveGateway($transaction->gateway->name);

        $gatewayService->refund($transaction->external_id);

        $transaction->update(['status' => TransactionStatus::REFUNDED]);

        return $transaction->fresh();
    }

    private function resolveGateway(string $name): PaymentGatewayInterface
    {
        $providers = config('gateways.providers', []);

        if (!isset($providers[$name])) {
            throw new Exception("Gateway {$name} não implementado.");
        }

        return app($providers[$name]);
    }
}
