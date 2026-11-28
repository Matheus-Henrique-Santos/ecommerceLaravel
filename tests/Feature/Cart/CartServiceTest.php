<?php

use App\Cart\CartException;
use App\Cart\CartService;
use App\Cart\SessionCartStore;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

function shoppingCart(): CartService
{
    return app(CartService::class);
}

describe('guest cart', function () {
    it('keeps items in the session', function () {
        $product = Product::factory()->create(['stock' => 10]);

        shoppingCart()->add($product, 2);
        shoppingCart()->add($product);

        expect(shoppingCart()->quantityOf($product))->toBe(3)
            ->and(session(SessionCartStore::KEY))->toBe([$product->id => 3]);

        $this->assertDatabaseCount('carts', 0);
    });

    it('computes count, subtotals and total from catalog prices', function () {
        $chair = Product::factory()->create(['name' => 'Cadeira', 'price_cents' => 19_990, 'stock' => 10]);
        $table = Product::factory()->create(['name' => 'Mesa', 'price_cents' => 89_900, 'stock' => 10]);

        shoppingCart()->add($chair, 4);
        shoppingCart()->add($table);

        $lines = shoppingCart()->lines();

        expect(shoppingCart()->count())->toBe(5)
            ->and(shoppingCart()->total()->cents)->toBe(169_860)
            ->and($lines->map(fn ($line) => $line->subtotal()->cents)->all())->toBe([79_960, 89_900]);
    });

    it('uses the current price instead of the price at the time of adding', function () {
        $product = Product::factory()->create(['price_cents' => 10_000, 'stock' => 5]);
        shoppingCart()->add($product);

        $product->update(['price_cents' => 12_500]);

        expect(shoppingCart()->total()->cents)->toBe(12_500);
    });
});

describe('quantity rules', function () {
    it('does not allow more units than the available stock', function () {
        $product = Product::factory()->create(['stock' => 3]);
        shoppingCart()->add($product, 2);

        expect(fn () => shoppingCart()->add($product, 2))
            ->toThrow(CartException::class, 'Só restam 3 unidades em estoque.');

        expect(shoppingCart()->quantityOf($product))->toBe(2);
    });

    it('reads stock from the database, not from a stale model', function () {
        $product = Product::factory()->create(['stock' => 5]);
        Product::query()->whereKey($product->id)->update(['stock' => 0]);

        expect(fn () => shoppingCart()->add($product))
            ->toThrow(CartException::class, 'Este produto está esgotado.');
    });

    it('rejects products that are not for sale', function (Product $product) {
        expect(fn () => shoppingCart()->add($product))
            ->toThrow(CartException::class, 'Este produto não está disponível.');
    })->with([
        'inactive product' => [fn () => Product::factory()->inactive()->create()],
        'inactive category' => [fn () => Product::factory()->for(Category::factory()->inactive())->create()],
        'soft-deleted product' => [fn () => tap(Product::factory()->create())->delete()],
    ]);

    it('rejects invalid quantities', function (int $quantity) {
        $product = Product::factory()->create(['stock' => 500]);

        expect(fn () => shoppingCart()->add($product, $quantity))->toThrow(CartException::class);
    })->with([0, -1, CartService::MAX_QUANTITY + 1]);

    it('updates the quantity and removes the item when it reaches zero', function () {
        $product = Product::factory()->create(['stock' => 10]);
        shoppingCart()->add($product, 2);

        shoppingCart()->update($product, 7);
        expect(shoppingCart()->quantityOf($product))->toBe(7);

        shoppingCart()->update($product, 0);
        expect(shoppingCart()->quantityOf($product))->toBe(0)
            ->and(shoppingCart()->lines())->toBeEmpty();
    });

    it('removes items and clears the cart', function () {
        [$first, $second] = Product::factory()->count(2)->create(['stock' => 10]);
        shoppingCart()->add($first);
        shoppingCart()->add($second);

        shoppingCart()->remove($first);
        expect(shoppingCart()->lines()->map(fn ($line) => $line->product->id)->all())->toBe([$second->id]);

        shoppingCart()->clear();
        expect(shoppingCart()->count())->toBe(0);
    });
});

it('hides products that became unavailable after being added', function () {
    $kept = Product::factory()->create(['stock' => 5]);
    $deactivated = Product::factory()->create(['stock' => 5]);
    shoppingCart()->add($kept);
    shoppingCart()->add($deactivated);

    $deactivated->update(['is_active' => false]);

    expect(shoppingCart()->lines())->toHaveCount(1)
        ->and(shoppingCart()->count())->toBe(1);
});

it('flags lines whose quantity exceeds the current stock', function () {
    $product = Product::factory()->create(['stock' => 5]);
    shoppingCart()->add($product, 4);

    $product->update(['stock' => 2]);

    expect(shoppingCart()->lines()->sole()->exceedsStock())->toBeTrue();
});

describe('customer cart', function () {
    it('persists items in the database for signed-in customers', function () {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($user);
        shoppingCart()->add($product, 2);
        shoppingCart()->add($product, 1);

        $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
        $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 3]);
        expect(session(SessionCartStore::KEY))->toBeNull();
    });

    it('keeps each customer cart isolated', function () {
        [$alice, $bob] = User::factory()->count(2)->create();
        $product = Product::factory()->create(['stock' => 10]);

        $this->actingAs($alice);
        shoppingCart()->add($product, 2);

        $this->actingAs($bob);
        expect(shoppingCart()->count())->toBe(0);

        shoppingCart()->add($product);
        shoppingCart()->clear();

        $this->actingAs($alice);
        expect(shoppingCart()->quantityOf($product))->toBe(2);
    });

    it('drops the cart when the customer account is deleted', function () {
        $user = User::factory()->create();
        $this->actingAs($user);
        shoppingCart()->add(Product::factory()->create(['stock' => 1]));

        $user->delete();

        $this->assertDatabaseCount('carts', 0);
        $this->assertDatabaseCount('cart_items', 0);
    });
});
