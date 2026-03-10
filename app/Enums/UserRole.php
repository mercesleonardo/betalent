<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN   = 'admin';
    case MANAGER = 'manager';
    case FINANCE = 'finance';
    case USER    = 'user';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN   => 'Administrador',
            self::MANAGER => 'Gerente',
            self::FINANCE => 'Financeiro',
            self::USER    => 'Usuário',
        };
    }
}
