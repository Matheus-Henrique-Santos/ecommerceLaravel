<?php

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\CatalogSeeder;

it('seeds the demo catalog', function () {
    $this->seed();

    expect(Category::count())->toBe(3)
        ->and(Product::count())->toBe(8)
        ->and(Product::active()->inCategory('celulares')->count())->toBe(3)
        ->and(Product::where('sku', 'NTB-PRO-14')->value('slug'))->toBe('notebook-pro-14-16-gb');
});

it('is idempotent', function () {
    $this->seed(CatalogSeeder::class);
    $this->seed(CatalogSeeder::class);

    expect(Category::count())->toBe(3)
        ->and(Product::count())->toBe(8);
});
