<?php

use App\Enums\Role;
use App\Livewire\Admin\Products\ProductEditor;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');

    $this->admin = User::factory()->admin()->create();
    $this->category = Category::factory()->create();
});

/**
 * Editor already filled with valid data, ready to be saved.
 */
function filledEditor(User $user, Category $category)
{
    return Livewire::actingAs($user)
        ->test(ProductEditor::class)
        ->set('form.name', 'Sofá Chesterfield')
        ->set('form.categoryId', (string) $category->id)
        ->set('form.sku', ' sof-100 ')
        ->set('form.price', '1.299,90')
        ->set('form.stock', '7')
        ->set('form.description', 'Couro legítimo, três lugares.');
}

it('creates a product with an uploaded image', function () {
    filledEditor($this->admin, $this->category)
        ->set('form.image', UploadedFile::fake()->image('sofa.jpg', 800, 600))
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.products.index'));

    $product = Product::query()->sole();

    expect($product)
        ->name->toBe('Sofá Chesterfield')
        ->slug->toBe('sofa-chesterfield')
        ->sku->toBe('SOF-100')
        ->price_cents->toBe(129_990)
        ->stock->toBe(7)
        ->is_active->toBeTrue()
        ->category_id->toBe($this->category->id)
        ->image_path->toStartWith(Product::IMAGE_DIRECTORY.'/');

    Storage::disk('public')->assertExists($product->image_path);
});

it('creates a product without an image and can save it hidden from the store', function () {
    filledEditor($this->admin, $this->category)
        ->set('form.isActive', false)
        ->set('form.description', '   ')
        ->call('save')
        ->assertHasNoErrors();

    expect(Product::query()->sole())
        ->image_path->toBeNull()
        ->description->toBeNull()
        ->is_active->toBeFalse();
});

it('loads the product into the form and updates it', function () {
    $product = Product::factory()->for($this->category)->create([
        'name' => 'Mesa Antiga',
        'sku' => 'MES-1',
        'price_cents' => 10_000,
        'stock' => 3,
    ]);

    Livewire::actingAs($this->admin)
        ->test(ProductEditor::class, ['product' => $product])
        ->assertSet('form.name', 'Mesa Antiga')
        ->assertSet('form.sku', 'MES-1')
        ->assertSet('form.price', '100,00')
        ->assertSet('form.stock', '3')
        ->set('form.name', 'Mesa Nova')
        ->set('form.price', '150.5')
        ->call('save')
        ->assertHasNoErrors()
        ->assertRedirect(route('admin.products.index'));

    expect($product->refresh())
        ->name->toBe('Mesa Nova')
        ->slug->toBe('mesa-antiga')
        ->sku->toBe('MES-1')
        ->price_cents->toBe(15_050);
});

it('replaces the previous image only after the new one is saved', function () {
    Storage::disk('public')->put('products/old.jpg', 'old');
    $product = Product::factory()->for($this->category)->create(['image_path' => 'products/old.jpg']);

    Livewire::actingAs($this->admin)
        ->test(ProductEditor::class, ['product' => $product])
        ->set('form.image', UploadedFile::fake()->image('new.png'))
        ->call('save')
        ->assertHasNoErrors();

    $product->refresh();

    expect($product->image_path)->not->toBe('products/old.jpg');
    Storage::disk('public')->assertExists($product->image_path);
    Storage::disk('public')->assertMissing('products/old.jpg');
});

it('validates the product fields', function (string $field, string $value) {
    filledEditor($this->admin, $this->category)
        ->set("form.{$field}", $value)
        ->call('save')
        ->assertHasErrors(["form.{$field}"]);

    expect(Product::query()->count())->toBe(0);
})->with([
    'missing name' => ['name', ''],
    'missing category' => ['categoryId', ''],
    'unknown category' => ['categoryId', '999999'],
    'non numeric category' => ['categoryId', 'abc'],
    'sku with spaces' => ['sku', 'SOF 100'],
    'missing price' => ['price', ''],
    'malformed price' => ['price', '12,345'],
    'zero price' => ['price', '0,00'],
    'negative stock' => ['stock', '-1'],
    'fractional stock' => ['stock', '1.5'],
]);

it('rejects a category that is in the trash', function () {
    $this->category->delete();

    filledEditor($this->admin, $this->category)
        ->call('save')
        ->assertHasErrors(['form.categoryId']);
});

it('rejects a SKU already used by another product, regardless of case', function () {
    Product::factory()->create(['sku' => 'SOF-100']);

    filledEditor($this->admin, $this->category)
        ->call('save')
        ->assertHasErrors(['form.sku']);
});

it('rejects a SKU held by a trashed product', function () {
    Product::factory()->create(['sku' => 'SOF-100'])->delete();

    filledEditor($this->admin, $this->category)
        ->call('save')
        ->assertHasErrors(['form.sku']);
});

it('rejects files that are not small images', function (Closure $file) {
    filledEditor($this->admin, $this->category)
        ->set('form.image', $file())
        ->call('save')
        ->assertHasErrors(['form.image']);

    expect(Product::query()->count())->toBe(0);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
})->with([
    'pdf' => [fn () => UploadedFile::fake()->create('manual.pdf', 100, 'application/pdf')],
    'gif' => [fn () => UploadedFile::fake()->image('animado.gif')],
    'larger than 2 MB' => [fn () => UploadedFile::fake()->image('enorme.jpg')->size(3_000)],
]);

it('re-checks permissions when saving', function () {
    $component = filledEditor($this->admin, $this->category);

    $this->admin->forceFill(['role' => Role::Customer])->save();

    $component->call('save')->assertForbidden();

    expect(Product::query()->count())->toBe(0);
});
