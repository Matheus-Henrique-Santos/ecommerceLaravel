<?php

use App\Enums\Role;
use App\Livewire\Admin\Products\ProductEditor;
use App\Livewire\Admin\Products\ProductTable;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Livewire;

/**
 * @return list<string>
 */
function adminListedNames(LengthAwarePaginator $products): array
{
    return $products->getCollection()->pluck('name')->all();
}

dataset('product admin pages', [
    'index' => [fn () => route('admin.products.index')],
    'create' => [fn () => route('admin.products.create')],
    'edit' => [fn () => route('admin.products.edit', Product::factory()->create())],
]);

it('redirects guests to the login page', function (string $url) {
    $this->get($url)->assertRedirect(route('login'));
})->with('product admin pages');

it('forbids customers', function (string $url) {
    $this->actingAs(User::factory()->create())->get($url)->assertForbidden();
})->with('product admin pages');

it('renders the back-office pages for admins', function () {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create(['name' => 'Cadeira Eames']);

    $this->actingAs($admin)->get(route('admin.products.index'))
        ->assertOk()
        ->assertSeeLivewire(ProductTable::class)
        ->assertSee('Cadeira Eames');

    $this->actingAs($admin)->get(route('admin.products.create'))
        ->assertOk()
        ->assertSeeLivewire(ProductEditor::class);

    $this->actingAs($admin)->get(route('admin.products.edit', $product))
        ->assertOk()
        ->assertSeeLivewire(ProductEditor::class)
        ->assertSee('Cadeira Eames');
});

it('returns 404 when editing a trashed product', function () {
    $product = Product::factory()->create();
    $product->delete();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.products.edit', $product))
        ->assertNotFound();
});

it('lists active and inactive products, newest first', function () {
    Product::factory()->create(['name' => 'Antigo']);
    Product::factory()->inactive()->create(['name' => 'Inativo']);
    Product::factory()->create(['name' => 'Recente']);

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(ProductTable::class)
        ->assertViewHas('products', fn ($products) => adminListedNames($products) === ['Recente', 'Inativo', 'Antigo']);
});

it('filters by status and searches by name or SKU', function () {
    Product::factory()->create(['name' => 'Poltrona', 'sku' => 'POL-1']);
    Product::factory()->inactive()->create(['name' => 'Puff', 'sku' => 'PUF-1']);

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(ProductTable::class)
        ->set('status', 'inativos')
        ->assertViewHas('products', fn ($products) => adminListedNames($products) === ['Puff'])
        ->set('status', 'ativos')
        ->assertViewHas('products', fn ($products) => adminListedNames($products) === ['Poltrona'])
        ->set('status', '')
        ->set('search', 'puf-')
        ->assertViewHas('products', fn ($products) => adminListedNames($products) === ['Puff']);
});

it('toggles whether a product is visible in the store', function () {
    $product = Product::factory()->create();

    $component = Livewire::actingAs(User::factory()->admin()->create())->test(ProductTable::class);

    $component->call('toggleActive', $product->id);
    expect($product->fresh()->is_active)->toBeFalse();

    $component->call('toggleActive', $product->id);
    expect($product->fresh()->is_active)->toBeTrue();
});

it('moves a product to the trash', function () {
    $product = Product::factory()->create();

    Livewire::actingAs(User::factory()->admin()->create())
        ->test(ProductTable::class)
        ->call('delete', $product->id)
        ->assertViewHas('products', fn ($products) => $products->total() === 0);

    expect($product->fresh()->trashed())->toBeTrue();
});

it('re-checks permissions on every table action', function (string $action) {
    $admin = User::factory()->admin()->create();
    $product = Product::factory()->create();

    $component = Livewire::actingAs($admin)->test(ProductTable::class);

    $admin->forceFill(['role' => Role::Customer])->save();

    $component->call($action, $product->id)->assertForbidden();

    $product = Product::withTrashed()->findOrFail($product->id);

    expect($product->is_active)->toBeTrue()
        ->and($product->trashed())->toBeFalse();
})->with(['toggleActive', 'delete']);
