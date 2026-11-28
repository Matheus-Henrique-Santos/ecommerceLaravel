<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

it('redirects guests to the login page', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('forbids customers from the back office', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

it('shows catalog figures to admins', function () {
    $category = Category::factory()->create();
    Product::factory()->for($category)->count(2)->create();
    Product::factory()->for($category)->inactive()->create();
    Product::factory()->for($category)->outOfStock()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertViewIs('admin.dashboard')
        ->assertViewHas('products', 4)
        ->assertViewHas('activeProducts', 3)
        ->assertViewHas('outOfStock', 1)
        ->assertViewHas('categories', 1);
});
