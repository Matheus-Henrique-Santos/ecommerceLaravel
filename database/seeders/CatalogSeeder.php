<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Demo catalog for local development. Idempotent: running it twice keeps
 * a single copy of each category and product (matched by slug / SKU).
 */
class CatalogSeeder extends Seeder
{
    /**
     * @var array<string, list<array{name: string, sku: string, price_cents: int, stock: int}>>
     */
    private const CATALOG = [
        'Celulares' => [
            ['name' => 'Smartphone Aurora 128 GB', 'sku' => 'CEL-AUR-128', 'price_cents' => 189_990, 'stock' => 25],
            ['name' => 'Smartphone Aurora 256 GB', 'sku' => 'CEL-AUR-256', 'price_cents' => 219_990, 'stock' => 12],
            ['name' => 'Smartphone Lite 64 GB', 'sku' => 'CEL-LIT-064', 'price_cents' => 99_990, 'stock' => 40],
        ],
        'Notebooks' => [
            ['name' => 'Notebook Pro 14" 16 GB', 'sku' => 'NTB-PRO-14', 'price_cents' => 649_900, 'stock' => 8],
            ['name' => 'Notebook Air 13" 8 GB', 'sku' => 'NTB-AIR-13', 'price_cents' => 389_900, 'stock' => 15],
        ],
        'Acessórios' => [
            ['name' => 'Fone Bluetooth com cancelamento de ruído', 'sku' => 'ACS-FON-BT', 'price_cents' => 49_990, 'stock' => 60],
            ['name' => 'Carregador USB-C 65 W', 'sku' => 'ACS-CRG-65W', 'price_cents' => 15_990, 'stock' => 100],
            ['name' => 'Capa de silicone Aurora', 'sku' => 'ACS-CAP-AUR', 'price_cents' => 7_990, 'stock' => 0],
        ],
    ];

    public function run(): void
    {
        foreach (self::CATALOG as $categoryName => $products) {
            $category = Category::firstOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName, 'is_active' => true],
            );

            foreach ($products as $product) {
                Product::updateOrCreate(
                    ['sku' => $product['sku']],
                    [...$product, 'category_id' => $category->id, 'is_active' => true],
                );
            }
        }
    }
}
