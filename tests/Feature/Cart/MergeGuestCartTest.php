<?php

use App\Cart\SessionCartStore;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;

function signIn(User $user): void
{
    test()->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect();
}

it('moves the guest cart into the customer cart on login', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock' => 10]);

    $this->withSession([SessionCartStore::KEY => [$product->id => 3]]);
    signIn($user);

    $this->assertAuthenticatedAs($user);
    $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 3]);
    expect(session(SessionCartStore::KEY))->toBeNull();
});

it('sums quantities already saved for the customer, capped at the stock', function () {
    $user = User::factory()->create();
    $sofa = Product::factory()->create(['stock' => 4]);
    $lamp = Product::factory()->create(['stock' => 10]);
    Cart::query()->create(['user_id' => $user->id])->items()->createMany([
        ['product_id' => $sofa->id, 'quantity' => 3],
        ['product_id' => $lamp->id, 'quantity' => 1],
    ]);

    $this->withSession([SessionCartStore::KEY => [$sofa->id => 2, $lamp->id => 2]]);
    signIn($user);

    expect($user->cart->items()->pluck('quantity', 'product_id')->all())
        ->toEqual([$sofa->id => 4, $lamp->id => 3]);
});

it('discards guest items that are no longer for sale', function () {
    $user = User::factory()->create();
    $inactive = Product::factory()->inactive()->create();
    $soldOut = Product::factory()->outOfStock()->create();

    $this->withSession([SessionCartStore::KEY => [$inactive->id => 1, $soldOut->id => 1]]);
    signIn($user);

    $this->assertDatabaseCount('cart_items', 0);
});

it('keeps the guest cart for customers who just registered', function () {
    $product = Product::factory()->create(['stock' => 10]);

    $this->withSession([SessionCartStore::KEY => [$product->id => 2]])
        ->post(route('register.store'), [
            'name' => 'Maria Souza',
            'email' => 'maria@example.com',
            'password' => 'segredo123',
            'password_confirmation' => 'segredo123',
        ])
        ->assertRedirect();

    $user = User::query()->sole();

    $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
    $this->assertDatabaseHas('cart_items', ['product_id' => $product->id, 'quantity' => 2]);
});

it('does not touch the customer cart when the guest cart is empty', function () {
    $user = User::factory()->create();

    signIn($user);

    $this->assertDatabaseCount('carts', 0);
});
