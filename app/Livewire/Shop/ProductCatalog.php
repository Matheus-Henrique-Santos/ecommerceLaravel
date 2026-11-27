<?php

namespace App\Livewire\Shop;

use App\Enums\ProductSort;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Storefront listing: search, category filter, sorting and pagination, all
 * mirrored in the query string so any state can be shared as a link.
 */
class ProductCatalog extends Component
{
    use WithPagination;

    public const PER_PAGE = 12;

    #[Url(as: 'busca', except: '')]
    public string $search = '';

    #[Url(as: 'categoria', except: '')]
    public string $category = '';

    #[Url(as: 'ordem', except: 'novidades')]
    public string $sort = 'novidades';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'category', 'sort'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'category', 'sort');
        $this->resetPage();
    }

    public function render(): View
    {
        return view('livewire.shop.product-catalog', [
            'products' => $this->products(),
            'categories' => $this->categories(),
            'sortOptions' => ProductSort::cases(),
        ]);
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    private function products(): LengthAwarePaginator
    {
        return Product::query()
            ->active()
            ->with('category:id,name,slug')
            ->when($this->category !== '', fn ($query) => $query->inCategory($this->category))
            ->search($this->search)
            ->sortedBy(ProductSort::fromQuery($this->sort))
            ->paginate(self::PER_PAGE);
    }

    /**
     * @return Collection<int, Category>
     */
    private function categories(): Collection
    {
        return Category::query()
            ->active()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }
}
