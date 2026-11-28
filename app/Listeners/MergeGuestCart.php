<?php

namespace App\Listeners;

use App\Cart\CartService;
use App\Models\User;
use Illuminate\Auth\Events\Login;

/**
 * Keeps what a visitor added to the cart when they sign in or register.
 */
class MergeGuestCart
{
    public function __construct(private readonly CartService $cart) {}

    public function handle(Login $event): void
    {
        if ($event->user instanceof User) {
            $this->cart->mergeGuestCartInto($event->user);
        }
    }
}
