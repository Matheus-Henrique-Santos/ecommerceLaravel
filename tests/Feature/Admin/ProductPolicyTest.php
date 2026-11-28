<?php

use App\Models\Product;
use App\Models\User;

it('lets only admins manage the catalog', function (string $ability, bool $withModel) {
    $product = Product::factory()->create();
    $arguments = $withModel ? $product : Product::class;

    expect(User::factory()->admin()->create()->can($ability, $arguments))->toBeTrue()
        ->and(User::factory()->create()->can($ability, $arguments))->toBeFalse();
})->with([
    'viewAny' => ['viewAny', false],
    'create' => ['create', false],
    'update' => ['update', true],
    'delete' => ['delete', true],
]);
