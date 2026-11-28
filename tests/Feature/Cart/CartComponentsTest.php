<?php

use App\Cart\CartService;
use App\Livewire\Cart\AddToCart;
use App\Livewire\Cart\CartCounter;
use App\Livewire\Cart\CartPage;
use App\Models\Product;
use Livewire\Livewire;

describe('add to cart', function () {
    it('is shown on the product page', function () {
        $product = Product::factory()->create();

        $this->get(route('site.product', $product->slug))
            ->assertSeeLivewire(AddToCart::class);
    });

    it('adds the chosen quantity and notifies the header', function () {
        $product = Product::factory()->create(['stock' => 10]);

        Livewire::test(AddToCart::class, ['product' => $product])
            ->set('quantity', 3)
            ->call('add')
            ->assertHasNoErrors()
            ->assertSet('quantity', 1)
            ->assertSee('Produto adicionado ao carrinho.')
            ->assertDispatched('cart-updated');

        expect(app(CartService::class)->quantityOf($product))->toBe(3);
    });

    it('validates the quantity', function (mixed $quantity) {
        $product = Product::factory()->create(['stock' => 500]);

        Livewire::test(AddToCart::class, ['product' => $product])
            ->set('quantity', $quantity)
            ->call('add')
            ->assertHasErrors('quantity')
            ->assertNotDispatched('cart-updated');
    })->with(['', 0, 'abc', CartService::MAX_QUANTITY + 1]);

    it('shows the stock error from the cart', function () {
        $product = Product::factory()->create(['stock' => 2]);

        Livewire::test(AddToCart::class, ['product' => $product])
            ->set('quantity', 5)
            ->call('add')
            ->assertHasErrors('quantity')
            ->assertSee('Só restam 2 unidades em estoque.')
            ->assertNotDispatched('cart-updated');
    });
});

describe('header counter', function () {
    it('shows the number of units in the cart', function () {
        $product = Product::factory()->create(['stock' => 10]);
        app(CartService::class)->add($product, 4);

        Livewire::test(CartCounter::class)
            ->assertSeeHtml('<span class="cart-counter-badge">4</span>');
    });

    it('hides the badge when the cart is empty', function () {
        Livewire::test(CartCounter::class)
            ->assertDontSeeHtml('cart-counter-badge');
    });

    it('is rendered in the site header', function () {
        $this->get(route('site.index'))->assertSeeLivewire(CartCounter::class);
    });
});

describe('cart page', function () {
    it('shows an empty state', function () {
        $this->get(route('site.cart'))
            ->assertOk()
            ->assertSeeLivewire(CartPage::class)
            ->assertSee('Seu carrinho está vazio.');
    });

    it('lists lines with subtotals and the total', function () {
        $chair = Product::factory()->create(['name' => 'Cadeira Eames', 'price_cents' => 45_000, 'stock' => 10]);
        $lamp = Product::factory()->create(['name' => 'Luminária', 'price_cents' => 12_990, 'stock' => 10]);
        app(CartService::class)->add($chair, 2);
        app(CartService::class)->add($lamp);

        Livewire::test(CartPage::class)
            ->assertSee('Cadeira Eames')
            ->assertSee('R$ 900,00')
            ->assertSee('Luminária')
            ->assertSee('R$ 1.029,90');
    });

    it('updates quantities', function () {
        $product = Product::factory()->create(['price_cents' => 10_000, 'stock' => 10]);
        app(CartService::class)->add($product);

        Livewire::test(CartPage::class)
            ->call('updateQuantity', $product->id, '3')
            ->assertHasNoErrors()
            ->assertSee('R$ 300,00')
            ->assertDispatched('cart-updated');

        expect(app(CartService::class)->quantityOf($product))->toBe(3);
    });

    it('rejects quantities above the stock or malformed', function (string $quantity, string $message) {
        $product = Product::factory()->create(['stock' => 2]);
        app(CartService::class)->add($product);

        Livewire::test(CartPage::class)
            ->call('updateQuantity', $product->id, $quantity)
            ->assertHasErrors("quantity.{$product->id}")
            ->assertSee($message)
            ->assertNotDispatched('cart-updated');

        expect(app(CartService::class)->quantityOf($product))->toBe(1);
    })->with([
        'above stock' => ['5', 'Só restam 2 unidades em estoque.'],
        'not a number' => ['dois', 'Informe uma quantidade entre 0 e 99.'],
        'negative' => ['-1', 'Informe uma quantidade entre 0 e 99.'],
    ]);

    it('removes a line when the quantity is set to zero or on remove', function (string $action) {
        $product = Product::factory()->create(['stock' => 10]);
        app(CartService::class)->add($product);

        $component = Livewire::test(CartPage::class);

        if ($action === 'zero') {
            $component->call('updateQuantity', $product->id, '0');
        } else {
            $component->call('remove', $product->id);
        }

        $component->assertSee('Seu carrinho está vazio.')->assertDispatched('cart-updated');
        expect(app(CartService::class)->count())->toBe(0);
    })->with(['zero', 'remove']);

    it('empties the cart', function () {
        app(CartService::class)->add(Product::factory()->create(['stock' => 10]), 2);

        Livewire::test(CartPage::class)
            ->call('clear')
            ->assertSee('Seu carrinho está vazio.');

        expect(app(CartService::class)->count())->toBe(0);
    });
});
