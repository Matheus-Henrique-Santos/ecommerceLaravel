<?php

namespace App\Livewire\Cart;

use App\Cart\CartService;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Header badge with the number of units in the cart, refreshed whenever
 * another component dispatches "cart-updated".
 */
class CartCounter extends Component
{
    #[On('cart-updated')]
    public function refreshCount(): void
    {
        // Re-rendering is enough: the count is always read from the cart.
    }

    public function render(): View
    {
        return view('livewire.cart.cart-counter', [
            'count' => $this->cart()->count(),
        ]);
    }

    private function cart(): CartService
    {
        return app(CartService::class);
    }
}
