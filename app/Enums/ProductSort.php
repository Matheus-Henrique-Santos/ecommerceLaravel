<?php

namespace App\Enums;

/**
 * Sort options exposed by the storefront. The backed value is what travels
 * in the query string, so it must stay stable once published.
 */
enum ProductSort: string
{
    case Newest = 'novidades';
    case PriceAsc = 'menor-preco';
    case PriceDesc = 'maior-preco';
    case Name = 'nome';

    public function label(): string
    {
        return match ($this) {
            self::Newest => 'Novidades',
            self::PriceAsc => 'Menor preço',
            self::PriceDesc => 'Maior preço',
            self::Name => 'Nome (A–Z)',
        };
    }

    /**
     * Resolves a raw query-string value, falling back to the default sort
     * instead of failing on tampered or outdated URLs.
     */
    public static function fromQuery(?string $value): self
    {
        return self::tryFrom((string) $value) ?? self::Newest;
    }
}
