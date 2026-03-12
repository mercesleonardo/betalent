<?php

namespace App\Http\Controllers\Api;

use App\Actions\Checkout\ProcessCheckoutAction;
use App\Exceptions\PaymentFailedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CheckoutRequest;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CheckoutController extends Controller
{
    public function __invoke(CheckoutRequest $request, ProcessCheckoutAction $action): JsonResponse
    {
        try {
            $transaction = $action->execute($request->validated());

            $transaction->load(['client', 'gateway', 'products']);

            return response()->json([
                'message'     => 'Compra realizada com sucesso!',
                'transaction' => $transaction,
            ], Response::HTTP_CREATED);
        } catch (PaymentFailedException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
