<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ShowProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('site.index');

Route::view('/shop', 'site.shop')->name('site.shop');
Route::get('/produto/{slug}', ShowProductController::class)->name('site.product');
Route::view('/sobre-nos', 'site.sobre-nos')->name('site.sobre-nos');
Route::view('/contato', 'site.contato')->name('site.contato');
Route::view('/cart', 'site.cart')->name('site.cart');
