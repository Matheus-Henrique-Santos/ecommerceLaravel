<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;

it('renders the registration form', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertViewIs('auth.register');
});

it('registers a customer and logs them in', function () {
    Event::fake([Registered::class]);

    $this->post(route('register.store'), [
        'name' => 'Maria Souza',
        'email' => '  Maria@Example.com ',
        'password' => 'segredo123',
        'password_confirmation' => 'segredo123',
    ])->assertRedirect(route('site.index'));

    $user = User::query()->sole();

    expect($user->email)->toBe('maria@example.com')
        ->and($user->role)->toBe(Role::Customer);

    $this->assertAuthenticatedAs($user);
    Event::assertDispatched(Registered::class);
});

it('ignores a role sent in the request payload', function () {
    $this->post(route('register.store'), [
        'name' => 'Intruso',
        'email' => 'intruso@example.com',
        'password' => 'segredo123',
        'password_confirmation' => 'segredo123',
        'role' => Role::Admin->value,
    ])->assertRedirect();

    expect(User::query()->sole()->isAdmin())->toBeFalse();
});

it('validates registration data', function (array $payload, string $field) {
    User::factory()->create(['email' => 'existente@example.com']);

    $this->from(route('register'))
        ->post(route('register.store'), array_merge([
            'name' => 'Fulano',
            'email' => 'novo@example.com',
            'password' => 'segredo123',
            'password_confirmation' => 'segredo123',
        ], $payload))
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors($field);

    $this->assertGuest();
})->with([
    'missing name' => [['name' => ''], 'name'],
    'invalid e-mail' => [['email' => 'nao-e-email'], 'email'],
    'duplicated e-mail (case-insensitive)' => [['email' => 'EXISTENTE@example.com'], 'email'],
    'short password' => [['password' => 'abc1', 'password_confirmation' => 'abc1'], 'password'],
    'unconfirmed password' => [['password_confirmation' => 'outra123'], 'password'],
]);

it('redirects authenticated users away from guest pages', function (string $route) {
    $this->actingAs(User::factory()->create())
        ->get(route($route))
        ->assertRedirect(route('site.index'));
})->with(['login', 'register']);
