<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\QueryException;

it('formats the price from cents in BRL', function (int $cents, string $expected) {
    $product = Product::factory()->make(['price_cents' => $cents]);

    expect($product->formatted_price)->toBe($expected);
})->with([
    'zero' => [0, 'R$ 0,00'],
    'cents only' => [99, 'R$ 0,99'],
    'thousands' => [129_990, 'R$ 1.299,90'],
    'millions' => [123_456_789, 'R$ 1.234.567,89'],
]);

it('stores the price as an integer amount of cents', function () {
    $product = Product::factory()->create(['price_cents' => 4_990]);

    expect($product->fresh()->price_cents)->toBe(4_990);
});

it('generates a unique slug from the name', function () {
    $first = Product::factory()->create(['name' => 'Fone Bluetooth']);
    $second = Product::factory()->create(['name' => 'Fone Bluetooth']);

    expect($first->slug)->toBe('fone-bluetooth')
        ->and($second->slug)->toBe('fone-bluetooth-2');
});

it('normalizes the SKU to upper case', function () {
    $product = Product::factory()->create(['sku' => '  cel-aur-128 ']);

    expect($product->sku)->toBe('CEL-AUR-128');
});

it('rejects duplicated SKUs', function () {
    Product::factory()->create(['sku' => 'ACS-001']);

    Product::factory()->create(['sku' => 'acs-001']);
})->throws(QueryException::class);

it('only lists active products from active categories', function () {
    $visible = Product::factory()->create();
    Product::factory()->inactive()->create();
    Product::factory()->for(Category::factory()->inactive())->create();
    Product::factory()->create()->delete();

    expect(Product::active()->pluck('id')->all())->toBe([$visible->id]);
});

it('filters products by category model, id or slug', function () {
    $phones = Category::factory()->create(['name' => 'Celulares']);
    $phone = Product::factory()->for($phones)->create();
    Product::factory()->create();

    expect(Product::inCategory($phones)->pluck('id')->all())->toBe([$phone->id])
        ->and(Product::inCategory($phones->id)->pluck('id')->all())->toBe([$phone->id])
        ->and(Product::inCategory('celulares')->pluck('id')->all())->toBe([$phone->id]);
});

it('checks stock availability', function () {
    $product = Product::factory()->make(['stock' => 3]);

    expect($product->isInStock())->toBeTrue()
        ->and($product->isInStock(3))->toBeTrue()
        ->and($product->isInStock(4))->toBeFalse()
        ->and(Product::factory()->outOfStock()->make()->isInStock())->toBeFalse();
});

it('prevents deleting a category that still has products', function () {
    $product = Product::factory()->create();

    $product->category->forceDelete();
})->throws(QueryException::class);
