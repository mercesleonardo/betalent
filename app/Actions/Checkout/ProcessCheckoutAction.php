<?php

namespace App\Actions\Checkout;

use App\Enums\TransactionStatus;
use App\Exceptions\PaymentFailedException;
use App\Models\{Client, Gateway, Product, Transaction};
use App\Services\Gateways\PaymentManager;
use Illuminate\Support\Facades\DB;

class ProcessCheckoutAction
{
    public function __construct(
        private readonly PaymentManager $paymentManager
    ) {
    }

    /**
     * Processa o checkout: calcula total no banco, cria Transaction em DB::transaction,
     * cobra via PaymentManager e atualiza status (PAID ou FAILED). Nunca confia no preço do front.
     */
    public function execute(array $data): Transaction
    {
        $totalAmount  = 0;
        $productsData = [];

        foreach ($data['products'] as $item) {
            $product = Product::findOrFail($item['id']);
            $totalAmount += $product->amount * $item['quantity'];

            $productsData[$product->id] = [
                'quantity'          => $item['quantity'],
                'historical_amount' => $product->amount,
            ];
        }

        $result = DB::transaction(function () use ($data, $totalAmount, $productsData): array {
            $client = Client::firstOrCreate(
                ['email' => $data['card_email']],
                ['name' => $data['card_name']]
            );

            $transaction = Transaction::create([
                'client_id'         => $client->id,
                'status'            => TransactionStatus::PENDING,
                'amount'            => $totalAmount,
                'card_last_numbers' => substr($data['card_number'], -4),
            ]);

            $transaction->products()->attach($productsData);

            $failureMessage = null;

            try {
                $paymentResult = $this->paymentManager->processPayment([
                    'amount'     => $totalAmount,
                    'name'       => $data['card_name'],
                    'email'      => $data['card_email'],
                    'cardNumber' => $data['card_number'],
                    'cvv'        => $data['card_cvv'],
                ]);

                $gatewayUsed = Gateway::where('name', $paymentResult['gateway_used'])->first();

                $transaction->update([
                    'status'      => TransactionStatus::PAID,
                    'gateway_id'  => $gatewayUsed?->id,
                    'external_id' => $paymentResult['response']['id'] ?? null,
                ]);
            } catch (\Throwable $e) {
                $transaction->update(['status' => TransactionStatus::FAILED]);
                $failureMessage = $e->getMessage();
            }

            return ['transaction' => $transaction, 'failure_message' => $failureMessage];
        });

        if ($result['failure_message'] !== null) {
            throw new PaymentFailedException('Pagamento recusado: ' . $result['failure_message']);
        }

        return $result['transaction'];
    }
}
