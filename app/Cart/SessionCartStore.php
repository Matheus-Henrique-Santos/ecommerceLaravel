<?php

namespace App\Cart;

use Illuminate\Contracts\Session\Session;

/**
 * Guest cart, kept in the session until the visitor signs in.
 */
final class SessionCartStore implements CartStore
{
    public const KEY = 'cart.items';

    public function __construct(private readonly Session $session) {}

    public function all(): array
    {
        $items = $this->session->get(self::KEY, []);

        return is_array($items) ? array_map(intval(...), $items) : [];
    }

    public function put(int $productId, int $quantity): void
    {
        $items = $this->all();
        $items[$productId] = $quantity;

        $this->session->put(self::KEY, $items);
    }

    public function forget(int $productId): void
    {
        $items = $this->all();
        unset($items[$productId]);

        $this->session->put(self::KEY, $items);
    }

    public function flush(): void
    {
        $this->session->forget(self::KEY);
    }
}
