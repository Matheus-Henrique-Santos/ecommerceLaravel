<?php

namespace App\Livewire\Admin\Products;

use App\Livewire\Forms\ProductForm;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Create/edit screen of the back office. Authorization runs on mount and
 * again on every save, because Livewire actions are separate requests that
 * do not go through the route middleware.
 */
class ProductEditor extends Component
{
    use WithFileUploads;

    public ProductForm $form;

    public function mount(?Product $product = null): void
    {
        if ($product?->exists) {
            Gate::authorize('update', $product);
            $this->form->setProduct($product);

            return;
        }

        Gate::authorize('create', Product::class);
    }

    public function save(): void
    {
        $existing = $this->form->product;

        $existing !== null
            ? Gate::authorize('update', $existing)
            : Gate::authorize('create', Product::class);

        $product = $this->form->persist();

        session()->flash('status', $existing !== null
            ? "Produto \"{$product->name}\" atualizado."
            : "Produto \"{$product->name}\" cadastrado.");

        $this->redirectRoute('admin.products.index');
    }

    public function render(): View
    {
        return view('livewire.admin.products.product-editor', [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name', 'is_active']),
        ]);
    }
}
