<?php

namespace App\Services\Gateways;

use Exception;
use Illuminate\Support\Facades\Http;

class GatewayOneService implements PaymentGatewayInterface
{
    public function charge(array $paymentData): array
    {
        $config = config('gateways.gateway_one');

        $loginResponse = Http::post($config['url'] . '/login', [
            'email' => $config['email'],
            'token' => $config['token'],
        ]);

        if (!$loginResponse->successful()) {
            throw new Exception('Falha na autenticação do Gateway 1');
        }

        $token = $loginResponse->json('token');

        $chargeResponse = Http::withToken($token)
            ->post($config['url'] . '/transactions', [
                'amount'     => $paymentData['amount'],
                'name'       => $paymentData['name'],
                'email'      => $paymentData['email'],
                'cardNumber' => $paymentData['cardNumber'],
                'cvv'        => $paymentData['cvv'],
            ]);

        if (!$chargeResponse->successful()) {
            throw new Exception('Pagamento recusado no Gateway 1: ' . $chargeResponse->body());
        }

        return $chargeResponse->json();
    }

    public function refund(string $transactionId): array
    {
        $config = config('gateways.gateway_one');

        $loginResponse = Http::post($config['url'] . '/login', [
            'email' => $config['email'],
            'token' => $config['token'],
        ]);

        if (!$loginResponse->successful()) {
            throw new Exception('Falha na autenticação do Gateway 1');
        }

        $token = $loginResponse->json('token');

        $response = Http::withToken($token)
            ->post($config['url'] . '/transactions/' . $transactionId . '/charge_back');

        if (!$response->successful()) {
            throw new Exception('Falha ao processar reembolso no Gateway 1: ' . $response->body());
        }

        return $response->json();
    }

    public function getName(): string
    {
        return 'GATEWAY_1';
    }
}
