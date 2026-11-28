<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Back-office product listing with search, status filter and quick actions
 * (activate/deactivate and move to trash). Every action re-checks the
 * ProductPolicy against the current user.
 */
class ProductTable extends Component
{
    use WithPagination;

    public const PER_PAGE = 15;

    #[Url(as: 'busca', except: '')]
    public string $search = '';

    #[Url(as: 'status', except: '')]
    public string $status = '';

    public function mount(): void
    {
        Gate::authorize('viewAny', Product::class);
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'status'], true)) {
            $this->resetPage();
        }
    }

    public function toggleActive(int $productId): void
    {
        $product = Product::query()->findOrFail($productId);

        Gate::authorize('update', $product);

        $product->update(['is_active' => ! $product->is_active]);
    }

    public function delete(int $productId): void
    {
        $product = Product::query()->findOrFail($productId);

        Gate::authorize('delete', $product);

        $product->delete();
    }

    public function render(): View
    {
        return view('livewire.admin.products.product-table', [
            'products' => $this->products(),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    private function products(): LengthAwarePaginator
    {
        return Product::query()
            ->with('category:id,name')
            ->search($this->search)
            ->when($this->status === 'ativos', fn ($query) => $query->where('is_active', true))
            ->when($this->status === 'inativos', fn ($query) => $query->where('is_active', false))
            ->latest('id')
            ->paginate(self::PER_PAGE);
    }
}
