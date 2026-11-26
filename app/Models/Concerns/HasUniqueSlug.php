<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

/**
 * Generates a URL slug from the model name when none is given.
 *
 * The slug is fixed after creation so public URLs stay stable when the
 * name changes. Soft-deleted rows are considered while checking for
 * collisions because the unique index still covers them.
 */
trait HasUniqueSlug
{
    public static function bootHasUniqueSlug(): void
    {
        static::creating(function (self $model) {
            $source = $model->slug ?: $model->name;

            $model->slug = static::uniqueSlugFor((string) $source, $model->getKey());
        });
    }

    public static function uniqueSlugFor(string $value, mixed $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::lower(Str::random(8));
        $slug = $base;
        $suffix = 2;

        while (static::slugExists($slug, $ignoreId)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    protected static function slugExists(string $slug, mixed $ignoreId): bool
    {
        return static::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
