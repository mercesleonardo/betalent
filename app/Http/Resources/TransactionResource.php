<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'status'            => $this->status?->value ?? $this->status,
            'amount'            => $this->amount,
            'amount_formatted'  => 'R$ ' . number_format($this->amount / 100, 2, ',', '.'),
            'card_last_numbers' => $this->card_last_numbers,
            'external_id'       => $this->external_id,
            'created_at'        => $this->created_at?->format('d/m/Y H:i:s'),
            'client'            => new ClientResource($this->whenLoaded('client')),
            'gateway'           => new GatewayResource($this->whenLoaded('gateway')),
            'products'          => ProductResource::collection($this->whenLoaded('products')),
        ];
    }
}
