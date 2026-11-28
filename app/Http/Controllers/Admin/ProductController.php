<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Contracts\View\View;

/**
 * Pages of the product back office. The interactive parts live in the
 * Livewire components; authorization is applied on the routes.
 */
class ProductController extends Controller
{
    public function index(): View
    {
        return view('admin.products.index');
    }

    public function create(): View
    {
        return view('admin.products.create');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', ['product' => $product]);
    }
}
