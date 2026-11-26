<?php

use App\Models\Category;
use App\Models\Product;

it('generates the slug from the name', function () {
    $category = Category::factory()->create(['name' => 'Acessórios de Áudio']);

    expect($category->slug)->toBe('acessorios-de-audio');
});

it('keeps an explicitly given slug', function () {
    $category = Category::factory()->create(['name' => 'Celulares', 'slug' => 'smartphones']);

    expect($category->slug)->toBe('smartphones');
});

it('suffixes the slug when it is already taken, including by trashed rows', function () {
    Category::factory()->create(['name' => 'Notebooks']);
    Category::factory()->create(['name' => 'Notebooks'])->delete();

    $third = Category::factory()->create(['name' => 'Notebooks']);

    expect($third->slug)->toBe('notebooks-3');
});

it('does not change the slug when the name changes', function () {
    $category = Category::factory()->create(['name' => 'Celulares']);

    $category->update(['name' => 'Smartphones']);

    expect($category->fresh()->slug)->toBe('celulares');
});

it('filters active categories', function () {
    $active = Category::factory()->create();
    Category::factory()->inactive()->create();

    expect(Category::active()->pluck('id')->all())->toBe([$active->id]);
});

it('has many products', function () {
    $category = Category::factory()->has(Product::factory()->count(2))->create();

    expect($category->products)->toHaveCount(2)
        ->each->toBeInstanceOf(Product::class);
});
