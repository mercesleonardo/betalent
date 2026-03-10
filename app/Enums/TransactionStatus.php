<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case PENDING  = 'pending';
    case PAID     = 'paid';
    case FAILED   = 'failed';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING  => 'Pendente',
            self::PAID     => 'Pago',
            self::FAILED   => 'Falhou',
            self::REFUNDED => 'Reembolsado',
        };
    }
}
