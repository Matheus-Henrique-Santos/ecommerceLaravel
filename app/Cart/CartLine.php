<?php

namespace App\Cart;

use App\Models\Product;
use App\ValueObjects\Money;

final readonly class CartLine
{
    public function __construct(
        public Product $product,
        public int $quantity,
    ) {}

    public function unitPrice(): Money
    {
        return $this->product->unitPrice();
    }

    public function subtotal(): Money
    {
        return $this->unitPrice()->multiply($this->quantity);
    }

    /**
     * Stock may drop after the item was added; checkout must reject the cart
     * while any line exceeds what is available.
     */
    public function exceedsStock(): bool
    {
        return ! $this->product->isInStock($this->quantity);
    }
}
