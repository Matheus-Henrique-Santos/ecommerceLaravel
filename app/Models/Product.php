<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

#[Fillable(['category_id', 'name', 'slug', 'sku', 'description', 'price_cents', 'stock', 'is_active'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, HasUniqueSlug, SoftDeletes;

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

    public function isInStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    /**
     * Price formatted in Brazilian Real, e.g. "R$ 1.299,90".
     */
    protected function formattedPrice(): Attribute
    {
        return Attribute::get(fn (): string => 'R$ '.number_format($this->price_cents / 100, 2, ',', '.'));
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
