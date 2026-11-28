<?php

namespace App\Cart;

/**
 * Storage for cart quantities keyed by product id. Prices and availability
 * are never stored here: they are always read from the catalog.
 */
interface CartStore
{
    /**
     * @return array<int, int> product id => quantity
     */
    public function all(): array;

    public function put(int $productId, int $quantity): void;

    public function forget(int $productId): void;

    public function flush(): void;
}
