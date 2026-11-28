<?php

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

beforeEach(fn () => Storage::fake('public'));

it('exposes the uploaded image URL or the catalog placeholder', function () {
    $withImage = Product::factory()->create(['image_path' => 'products/sofa.jpg']);
    $withoutImage = Product::factory()->create();

    expect($withImage->image_url)->toBe(Storage::disk('public')->url('products/sofa.jpg'))
        ->and($withoutImage->image_url)->toBe(asset(Product::PLACEHOLDER_IMAGE));
});

it('ignores image_path in mass assignment', function () {
    $product = new Product(['name' => 'Banqueta', 'image_path' => '../../.env']);

    expect($product->image_path)->toBeNull();
});

it('shows the product image in the storefront', function () {
    $product = Product::factory()->create(['image_path' => 'products/sofa.jpg']);
    $url = Storage::disk('public')->url('products/sofa.jpg');

    $this->get(route('site.product', $product->slug))->assertOk()->assertSee($url, escape: false);
    $this->get(route('site.shop'))->assertOk()->assertSee($url, escape: false);
});
