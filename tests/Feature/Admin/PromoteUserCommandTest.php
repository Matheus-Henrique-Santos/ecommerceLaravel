<?php

use App\Models\User;

it('promotes an existing user to admin', function () {
    $user = User::factory()->create(['email' => 'ana@example.com']);

    $this->artisan('user:promote', ['email' => 'ana@example.com'])->assertSuccessful();

    expect($user->fresh()->isAdmin())->toBeTrue();
});

it('is a no-op for users who are already admins', function () {
    User::factory()->admin()->create(['email' => 'ana@example.com']);

    $this->artisan('user:promote', ['email' => 'ana@example.com'])
        ->expectsOutputToContain('already an admin')
        ->assertSuccessful();
});

it('fails for unknown e-mails', function () {
    $this->artisan('user:promote', ['email' => 'ninguem@example.com'])->assertFailed();
});
