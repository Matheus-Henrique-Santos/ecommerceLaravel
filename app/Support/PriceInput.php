<?php

namespace App\Support;

/**
 * Converts prices typed in back-office forms to integer cents and back.
 *
 * Accepted formats: "1299", "1299,9", "1.299,90" (Brazilian notation) and
 * "1299.90" (dot as decimal separator, at most two decimals). A dot followed
 * by exactly three digits is a thousands separator, so "1.299" is R$ 1.299,00.
 */
final class PriceInput
{
    public const MAX_CENTS = 99_999_999;

    public static function toCents(string $input): ?int
    {
        $value = trim(str_replace(['R$', ' '], '', $input));

        if (preg_match('/^\d{1,3}(?:\.\d{3})*(?:,\d{1,2})?$/', $value) === 1
            || preg_match('/^\d+(?:,\d{1,2})?$/', $value) === 1) {
            [$integer, $decimals] = array_pad(explode(',', str_replace('.', '', $value)), 2, '');
        } elseif (preg_match('/^\d+\.\d{1,2}$/', $value) === 1) {
            [$integer, $decimals] = explode('.', $value);
        } else {
            return null;
        }

        $integer = ltrim($integer, '0');

        if (strlen($integer) > 6) {
            return null;
        }

        $cents = (int) $integer * 100 + (int) str_pad($decimals, 2, '0');

        return $cents <= self::MAX_CENTS ? $cents : null;
    }

    public static function fromCents(int $cents): string
    {
        return number_format($cents / 100, 2, ',', '.');
    }
}
