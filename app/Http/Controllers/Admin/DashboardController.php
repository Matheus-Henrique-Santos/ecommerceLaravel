<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'products' => Product::query()->count(),
            'activeProducts' => Product::query()->where('is_active', true)->count(),
            'outOfStock' => Product::query()->where('stock', 0)->count(),
            'categories' => Category::query()->count(),
        ]);
    }
}
