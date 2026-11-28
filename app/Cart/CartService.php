<?php

namespace App\Cart;

use App\Models\Product;
use App\Models\User;
use App\ValueObjects\Money;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Entry point for every cart operation. The storage is chosen per request:
 * guests use the session and signed-in customers use the database, so
 * callers never need to know who is shopping.
 */
class CartService
{
    /**
     * Upper bound per product, protecting stock from a single cart.
     */
    public const MAX_QUANTITY = 99;

    public function __construct(
        private readonly AuthFactory $auth,
        private readonly Session $session,
    ) {}

    /**
     * Adds units of a product on top of what is already in the cart.
     *
     * @throws CartException
     */
    public function add(Product $product, int $quantity = 1): void
    {
        if ($quantity < 1) {
            throw CartException::invalidQuantity(self::MAX_QUANTITY);
        }

        $this->update($product, $this->quantityOf($product) + $quantity);
    }

    /**
     * Sets the quantity of a product; zero removes it from the cart.
     *
     * @throws CartException
     */
    public function update(Product $product, int $quantity): void
    {
        if ($quantity < 0 || $quantity > self::MAX_QUANTITY) {
            throw CartException::invalidQuantity(self::MAX_QUANTITY);
        }

        if ($quantity === 0) {
            $this->remove($product);

            return;
        }

        $current = $this->purchasable($product->getKey());

        if ($current === null) {
            throw CartException::unavailable();
        }

        if (! $current->isInStock($quantity)) {
            throw CartException::insufficientStock($current);
        }

        $this->store()->put($current->id, $quantity);
    }

    public function remove(Product|int $product): void
    {
        $this->store()->forget($product instanceof Product ? $product->id : $product);
    }

    public function clear(): void
    {
        $this->store()->flush();
    }

    public function quantityOf(Product $product): int
    {
        return $this->store()->all()[$product->id] ?? 0;
    }

    /**
     * Lines with fresh catalog data. Products that were deactivated or removed
     * after being added are left out.
     *
     * @return Collection<int, CartLine>
     */
    public function lines(): Collection
    {
        return $this->linesFrom($this->store()->all());
    }

    /**
     * Total number of units, shown in the header counter.
     */
    public function count(): int
    {
        return (int) $this->lines()->sum('quantity');
    }

    public function total(): Money
    {
        return self::sum($this->lines());
    }

    /**
     * @param  Collection<int, CartLine>  $lines
     */
    public static function sum(Collection $lines): Money
    {
        return $lines->reduce(
            fn (Money $total, CartLine $line) => $total->add($line->subtotal()),
            Money::zero(),
        );
    }

    /**
     * Moves the guest cart into the customer's persistent cart. Quantities of
     * the same product are summed and capped at the available stock, and the
     * guest cart is emptied so the merge never runs twice.
     */
    public function mergeGuestCartInto(User $user): void
    {
        $guest = new SessionCartStore($this->session);
        $items = $guest->all();
        $guest->flush();

        if ($items === []) {
            return;
        }

        $customer = new DatabaseCartStore($user);
        $existing = $customer->all();

        foreach ($this->linesFrom($items) as $line) {
            $quantity = min(
                ($existing[$line->product->id] ?? 0) + $line->quantity,
                $line->product->stock,
            );

            if ($quantity > 0) {
                $customer->put($line->product->id, $quantity);
            }
        }
    }

    private function store(): CartStore
    {
        $user = $this->auth->guard()->user();

        return $user instanceof User
            ? new DatabaseCartStore($user)
            : new SessionCartStore($this->session);
    }

    /**
     * Re-reads the product so stock and visibility are never taken from a
     * stale model instance.
     */
    private function purchasable(int $productId): ?Product
    {
        return Product::query()->active()->find($productId);
    }

    /**
     * @param  array<int, int>  $items
     * @return Collection<int, CartLine>
     */
    private function linesFrom(array $items): Collection
    {
        if ($items === []) {
            return new Collection;
        }

        $products = Product::query()
            ->active()
            ->whereKey(array_keys($items))
            ->orderBy('name')
            ->get();

        return $products
            ->map(fn (Product $product) => new CartLine($product, $items[$product->id]))
            ->values()
            ->toBase();
    }
}
