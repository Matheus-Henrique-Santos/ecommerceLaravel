<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Contracts\View\View;

class ShowProductController extends Controller
{
    /**
     * Product detail page. Inactive products, products from inactive
     * categories and soft-deleted products all resolve to 404.
     */
    public function __invoke(string $slug): View
    {
        $product = Product::query()
            ->active()
            ->with('category:id,name,slug')
            ->where('slug', $slug)
            ->firstOrFail();

        return view('site.product', ['product' => $product]);
    }
}
