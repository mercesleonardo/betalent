<?php

namespace App\Http\Controllers\Api;

use App\Actions\Transaction\RefundTransactionAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TransactionController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $transactions = Transaction::with(['client', 'gateway'])->latest()->get();

        return TransactionResource::collection($transactions);
    }

    public function show(Transaction $transaction): TransactionResource
    {
        $transaction->load(['client', 'gateway', 'products']);

        return new TransactionResource($transaction);
    }

    public function refund(Transaction $transaction, RefundTransactionAction $action): JsonResponse
    {
        Gate::authorize('manage-finances');

        try {
            $refundedTransaction = $action->execute($transaction);

            return response()->json([
                'message'     => 'Reembolso efetuado com sucesso.',
                'transaction' => new TransactionResource($refundedTransaction),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
