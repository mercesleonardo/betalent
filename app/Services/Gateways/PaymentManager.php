<?php

namespace App\Services\Gateways;

use App\Models\Gateway;
use App\Observers\GatewayObserver;
use Exception;
use Illuminate\Support\Facades\Cache;

class PaymentManager
{
    public function processPayment(array $paymentData): array
    {
        $activeGateways = Cache::rememberForever(GatewayObserver::ACTIVE_LIST_CACHE_KEY, function () {
            return Gateway::active()->orderBy('priority')->get();
        });

        $lastException = null;

        foreach ($activeGateways as $gatewayModel) {
            try {
                $gatewayService = $this->resolveGateway($gatewayModel->name);

                $response = $gatewayService->charge($paymentData);

                return [
                    'success'      => true,
                    'gateway_used' => $gatewayModel->name,
                    'response'     => $response,
                ];
            } catch (Exception $e) {
                $lastException = $e;
            }
        }

        throw new Exception('Todos os gateways falharam. Último erro: ' . $lastException?->getMessage());
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
