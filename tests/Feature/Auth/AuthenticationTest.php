<?php

use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;

it('renders the login form', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertViewIs('auth.login');
});

it('logs a customer in and sends them to the storefront', function () {
    $user = User::factory()->create();

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertRedirect(route('site.index'));

    $this->assertAuthenticatedAs($user);
});

it('sends admins to the back office after login', function () {
    $admin = User::factory()->admin()->create();

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));
});

it('redirects to the originally requested page after login', function () {
    $admin = User::factory()->admin()->create();

    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));

    $this->post(route('login.store'), [
        'email' => $admin->email,
        'password' => 'password',
    ])->assertRedirect(route('admin.dashboard'));
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create();

    $this->from(route('login'))
        ->post(route('login.store'), [
            'email' => $user->email,
            'password' => 'senha-errada',
        ])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('locks the account out after too many failed attempts', function () {
    $user = User::factory()->create();

    foreach (range(1, LoginRequest::MAX_ATTEMPTS) as $attempt) {
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'errada']);
    }

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('logs the user out and invalidates the session', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect(route('site.index'));

    $this->assertGuest();
});

it('shows account links according to authentication state', function () {
    $this->get(route('site.shop'))
        ->assertSee(route('login'), escape: false)
        ->assertDontSee(route('admin.dashboard'), escape: false);

    $this->actingAs(User::factory()->admin()->create(['name' => 'Ana Admin']))
        ->get(route('site.shop'))
        ->assertSee('Ana Admin')
        ->assertSee(route('admin.dashboard'), escape: false)
        ->assertSee(route('logout'), escape: false);
});
