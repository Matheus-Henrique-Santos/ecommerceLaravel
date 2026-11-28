<?php

namespace App\Enums;

/**
 * Access level of a user account. Stored as a string column so new roles
 * can be added without a schema change.
 */
enum Role: string
{
    case Admin = 'admin';
    case Customer = 'customer';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Customer => 'Cliente',
        };
    }
}
