<?php

use App\Models\Category;
use App\Models\Product;

it('shows an active product by slug', function () {
    $category = Category::factory()->create(['name' => 'Sala de Estar']);
    $product = Product::factory()->for($category)->create([
        'name' => 'Sofá Três Lugares',
        'sku' => 'sof-3l',
        'price_cents' => 249_990,
        'stock' => 4,
    ]);

    $this->get(route('site.product', $product->slug))
        ->assertOk()
        ->assertViewIs('site.product')
        ->assertSee('Sofá Três Lugares')
        ->assertSee('SOF-3L')
        ->assertSee('R$ 2.499,90')
        ->assertSee('4 em estoque')
        ->assertSee(route('site.shop', ['categoria' => $category->slug]), escape: false);
});

it('flags products without stock as sold out', function () {
    $product = Product::factory()->outOfStock()->create();

    $this->get(route('site.product', $product->slug))
        ->assertOk()
        ->assertSee('Esgotado');
});

it('links each catalog card to its product page', function () {
    $product = Product::factory()->create();

    $this->get(route('site.shop'))
        ->assertSee(route('site.product', $product->slug), escape: false);
});

it('returns 404 for products that are not publicly visible', function (Product $product) {
    $this->get(route('site.product', $product->slug))->assertNotFound();
})->with([
    'inactive product' => [fn () => Product::factory()->inactive()->create()],
    'inactive category' => [fn () => Product::factory()->for(Category::factory()->inactive())->create()],
    'soft-deleted product' => [fn () => tap(Product::factory()->create())->delete()],
]);

it('returns 404 for unknown slugs', function () {
    $this->get(route('site.product', 'nao-existe'))->assertNotFound();
});
