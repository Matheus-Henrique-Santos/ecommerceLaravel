<?php

namespace App\Models;

use App\Enums\ProductSort;
use App\Models\Concerns\HasUniqueSlug;
use App\ValueObjects\Money;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * `image_path` is intentionally not fillable: it is only set by the back
 * office after the upload has been validated and stored.
 */
#[Fillable(['category_id', 'name', 'slug', 'sku', 'description', 'price_cents', 'stock', 'is_active'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasUniqueSlug, SoftDeletes;

    public const IMAGE_DISK = 'public';

    public const IMAGE_DIRECTORY = 'products';

    public const PLACEHOLDER_IMAGE = 'img/main/product/product-1.svg';

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Products that can be shown in the storefront: active and inside an
     * active category.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true)
            ->whereHas('category', fn (Builder $category) => $category->where('is_active', true));
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function inCategory(Builder $query, Category|int|string $category): void
    {
        match (true) {
            $category instanceof Category => $query->whereBelongsTo($category),
            is_int($category) => $query->where('category_id', $category),
            default => $query->whereHas('category', fn (Builder $q) => $q->where('slug', $category)),
        };
    }

    /**
     * Case-insensitive search by name or SKU. LIKE wildcards typed by the
     * user are escaped so "%" and "_" match literally.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function search(Builder $query, ?string $term): void
    {
        $term = trim((string) $term);

        if ($term === '') {
            return;
        }

        $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($term));
        $pattern = "%{$escaped}%";

        // Explicit ESCAPE keeps the behaviour identical on PostgreSQL and SQLite.
        $query->where(fn (Builder $q) => $q
            ->whereRaw("lower(name) like ? escape '!'", [$pattern])
            ->orWhereRaw("lower(sku) like ? escape '!'", [$pattern]));
    }

    /**
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function sortedBy(Builder $query, ProductSort $sort): void
    {
        match ($sort) {
            ProductSort::Newest => $query->latest()->latest('id'),
            ProductSort::PriceAsc => $query->orderBy('price_cents')->orderBy('id'),
            ProductSort::PriceDesc => $query->orderByDesc('price_cents')->orderBy('id'),
            ProductSort::Name => $query->orderBy('name')->orderBy('id'),
        };
    }

    public function isInStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    public function unitPrice(): Money
    {
        return Money::fromCents($this->price_cents);
    }

    /**
     * Price formatted in Brazilian Real, e.g. "R$ 1.299,90".
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::get(fn (): string => $this->unitPrice()->format());
    }

    /**
     * Public URL of the uploaded image, falling back to the catalog placeholder.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn (): string => $this->image_path
            ? Storage::disk(self::IMAGE_DISK)->url($this->image_path)
            : asset(self::PLACEHOLDER_IMAGE));
    }

    /**
     * SKUs are stored upper-cased and trimmed so lookups are case-insensitive.
     */
    protected function sku(): Attribute
    {
        return Attribute::set(fn (string $value): string => Str::upper(trim($value)));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price_cents' => 'integer',
            'stock' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
