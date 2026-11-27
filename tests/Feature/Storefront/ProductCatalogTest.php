<?php

use App\Enums\ProductSort;
use App\Livewire\Shop\ProductCatalog;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Livewire;

/**
 * @return list<string>
 */
function listedNames(LengthAwarePaginator $products): array
{
    return $products->getCollection()->pluck('name')->all();
}

it('renders the catalog on the shop page', function () {
    Product::factory()->create(['name' => 'Sofá Retrátil']);

    $this->get(route('site.shop'))
        ->assertOk()
        ->assertSeeLivewire(ProductCatalog::class)
        ->assertSee('Sofá Retrátil');
});

it('only lists active products from active categories', function () {
    Product::factory()->create(['name' => 'Mesa Visível']);
    Product::factory()->inactive()->create(['name' => 'Mesa Inativa']);
    Product::factory()->for(Category::factory()->inactive())->create(['name' => 'Mesa Oculta']);

    Livewire::test(ProductCatalog::class)
        ->assertSee('Mesa Visível')
        ->assertDontSee('Mesa Inativa')
        ->assertDontSee('Mesa Oculta');
});

it('filters by category slug', function () {
    $chairs = Category::factory()->create(['name' => 'Cadeiras']);
    Product::factory()->for($chairs)->create(['name' => 'Cadeira Gamer']);
    Product::factory()->create(['name' => 'Luminária']);

    Livewire::test(ProductCatalog::class)
        ->set('category', 'cadeiras')
        ->assertViewHas('products', fn ($products) => listedNames($products) === ['Cadeira Gamer']);
});

it('searches by name or SKU, case-insensitively', function () {
    Product::factory()->create(['name' => 'Poltrona Bergère', 'sku' => 'POL-001']);
    Product::factory()->create(['name' => 'Rack de TV', 'sku' => 'RCK-777']);

    Livewire::test(ProductCatalog::class)
        ->set('search', 'poltrona')
        ->assertViewHas('products', fn ($products) => listedNames($products) === ['Poltrona Bergère'])
        ->set('search', 'rck-7')
        ->assertViewHas('products', fn ($products) => listedNames($products) === ['Rack de TV']);
});

it('treats LIKE wildcards in the search term literally', function () {
    Product::factory()->create(['name' => 'Desconto 100% Algodão']);
    Product::factory()->create(['name' => 'Cortina Blackout']);

    Livewire::test(ProductCatalog::class)
        ->set('search', '%')
        ->assertViewHas('products', fn ($products) => listedNames($products) === ['Desconto 100% Algodão'])
        ->set('search', '_')
        ->assertViewHas('products', fn ($products) => $products->total() === 0);
});

it('sorts products', function (ProductSort $sort, array $expected) {
    Product::factory()->create(['name' => 'Banco', 'price_cents' => 30_000, 'created_at' => now()->subDays(2)]);
    Product::factory()->create(['name' => 'Armário', 'price_cents' => 90_000, 'created_at' => now()->subDay()]);
    Product::factory()->create(['name' => 'Cômoda', 'price_cents' => 10_000, 'created_at' => now()]);

    Livewire::test(ProductCatalog::class)
        ->set('sort', $sort->value)
        ->assertViewHas('products', fn ($products) => listedNames($products) === $expected);
})->with([
    'newest' => [ProductSort::Newest, ['Cômoda', 'Armário', 'Banco']],
    'cheapest first' => [ProductSort::PriceAsc, ['Cômoda', 'Banco', 'Armário']],
    'most expensive first' => [ProductSort::PriceDesc, ['Armário', 'Banco', 'Cômoda']],
    'name' => [ProductSort::Name, ['Armário', 'Banco', 'Cômoda']],
]);

it('falls back to the default sort for unknown values', function () {
    expect(ProductSort::fromQuery('drop-table'))->toBe(ProductSort::Newest)
        ->and(ProductSort::fromQuery(null))->toBe(ProductSort::Newest)
        ->and(ProductSort::fromQuery('menor-preco'))->toBe(ProductSort::PriceAsc);

    Product::factory()->create();

    Livewire::test(ProductCatalog::class)
        ->set('sort', 'drop-table')
        ->assertOk()
        ->assertViewHas('products', fn ($products) => $products->total() === 1);
});

it('hydrates filters from the query string', function () {
    $tables = Category::factory()->create(['name' => 'Mesas']);
    Product::factory()->for($tables)->create(['name' => 'Mesa Jantar', 'price_cents' => 80_000]);
    Product::factory()->for($tables)->create(['name' => 'Mesa Centro', 'price_cents' => 20_000]);
    Product::factory()->create(['name' => 'Mesa Escritório']);

    Livewire::withQueryParams(['categoria' => 'mesas', 'ordem' => 'menor-preco', 'busca' => 'mesa'])
        ->test(ProductCatalog::class)
        ->assertSet('category', 'mesas')
        ->assertSet('sort', 'menor-preco')
        ->assertSet('search', 'mesa')
        ->assertViewHas('products', fn ($products) => listedNames($products) === ['Mesa Centro', 'Mesa Jantar']);
});

it('paginates the listing and goes back to the first page when filters change', function () {
    Product::factory()->count(ProductCatalog::PER_PAGE + 1)->create();

    Livewire::test(ProductCatalog::class)
        ->assertViewHas('products', fn ($products) => $products->count() === ProductCatalog::PER_PAGE
            && $products->total() === ProductCatalog::PER_PAGE + 1)
        ->call('gotoPage', 2)
        ->assertViewHas('products', fn ($products) => $products->currentPage() === 2 && $products->count() === 1)
        ->set('sort', ProductSort::PriceAsc->value)
        ->assertViewHas('products', fn ($products) => $products->currentPage() === 1);
});

it('clears all filters at once', function () {
    Livewire::test(ProductCatalog::class)
        ->set('search', 'sofá')
        ->set('category', 'salas')
        ->set('sort', ProductSort::PriceDesc->value)
        ->call('clearFilters')
        ->assertSet('search', '')
        ->assertSet('category', '')
        ->assertSet('sort', ProductSort::Newest->value);
});

it('shows an empty state when nothing matches', function () {
    Livewire::test(ProductCatalog::class)
        ->set('search', 'inexistente')
        ->assertSee('Nenhum produto encontrado');
});
