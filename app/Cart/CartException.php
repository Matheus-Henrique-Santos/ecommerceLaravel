<?php

namespace App\Cart;

use App\Models\Product;
use DomainException;

/**
 * Business rule violations raised by the cart. Messages are user facing.
 */
final class CartException extends DomainException
{
    public static function unavailable(): self
    {
        return new self('Este produto não está disponível.');
    }

    public static function insufficientStock(Product $product): self
    {
        return new self(match ($product->stock) {
            0 => 'Este produto está esgotado.',
            1 => 'Só resta 1 unidade em estoque.',
            default => "Só restam {$product->stock} unidades em estoque.",
        });
    }

    public static function invalidQuantity(int $max): self
    {
        return new self("Informe uma quantidade entre 1 e {$max}.");
    }
}
