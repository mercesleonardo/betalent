<?php

namespace App\Services\Gateways;

use Exception;
use Illuminate\Support\Facades\Http;

class GatewayTwoService implements PaymentGatewayInterface
{
    public function charge(array $paymentData): array
    {
        $payload = [
            'valor'        => $paymentData['amount'],
            'nome'         => $paymentData['name'],
            'email'        => $paymentData['email'],
            'numeroCartao' => $paymentData['cardNumber'],
            'cvv'          => $paymentData['cvv'],
        ];

        $baseUrl = config('gateways.gateway_two.url');

        $response = Http::withHeaders($this->getAuthHeaders())
            ->post($baseUrl . '/transacoes', $payload);

        if (!$response->successful()) {
            throw new Exception('Pagamento recusado no Gateway 2: ' . $response->body());
        }

        return $response->json();
    }

    public function refund(string $transactionId): array
    {
        $baseUrl = config('gateways.gateway_two.url');

        $response = Http::withHeaders($this->getAuthHeaders())
            ->post($baseUrl . '/transacoes/reembolso', [
                'id' => $transactionId,
            ]);

        if (!$response->successful()) {
            throw new Exception('Falha ao processar reembolso no Gateway 2: ' . $response->body());
        }

        return $response->json();
    }

    public function getName(): string
    {
        return 'GATEWAY_2';
    }

    private function getAuthHeaders(): array
    {
        $config = config('gateways.gateway_two');

        return [
            'Gateway-Auth-Token'  => $config['token'],
            'Gateway-Auth-Secret' => $config['secret'],
            'Accept'              => 'application/json',
            'Content-Type'        => 'application/json',
        ];
    }
}
