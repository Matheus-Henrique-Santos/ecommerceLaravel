<?php

namespace App\Cart;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Cart of an authenticated customer, persisted so it follows them across
 * devices and sessions. The cart row is only created on the first write.
 */
final class DatabaseCartStore implements CartStore
{
    public function __construct(private readonly User $user) {}

    public function all(): array
    {
        return $this->items()
            ->pluck('quantity', 'product_id')
            ->map(intval(...))
            ->all();
    }

    public function put(int $productId, int $quantity): void
    {
        $cart = Cart::query()->firstOrCreate(['user_id' => $this->user->id]);

        $cart->items()->updateOrCreate(
            ['product_id' => $productId],
            ['quantity' => $quantity],
        );
    }

    public function forget(int $productId): void
    {
        $this->items()->where('product_id', $productId)->delete();
    }

    public function flush(): void
    {
        $this->items()->delete();
    }

    /**
     * @return Builder<CartItem>
     */
    private function items(): Builder
    {
        return CartItem::query()
            ->whereHas('cart', fn (Builder $cart) => $cart->whereBelongsTo($this->user));
    }
}
