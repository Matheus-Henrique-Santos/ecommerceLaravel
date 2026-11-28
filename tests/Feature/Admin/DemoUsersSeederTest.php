<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

it('seeds one admin and one customer idempotently', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->count())->toBe(2)
        ->and(User::query()->where('email', 'admin@example.com')->sole()->isAdmin())->toBeTrue()
        ->and(User::query()->where('email', 'cliente@example.com')->sole()->isAdmin())->toBeFalse();
});
