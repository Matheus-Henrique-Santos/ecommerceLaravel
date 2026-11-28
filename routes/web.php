<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShowProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('site.index');

Route::view('/shop', 'site.shop')->name('site.shop');
Route::get('/produto/{slug}', ShowProductController::class)->name('site.product');
Route::view('/sobre-nos', 'site.sobre-nos')->name('site.sobre-nos');
Route::view('/contato', 'site.contato')->name('site.contato');
Route::view('/cart', 'site.cart')->name('site.cart');

Route::middleware('guest')->group(function () {
    Route::get('/entrar', [LoginController::class, 'create'])->name('login');
    Route::post('/entrar', [LoginController::class, 'store'])->name('login.store');
    Route::get('/cadastro', [RegisterController::class, 'create'])->name('register');
    Route::post('/cadastro', [RegisterController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('register.store');
});

Route::post('/sair', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'can:access-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
    });
