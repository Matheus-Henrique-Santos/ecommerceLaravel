<?php

namespace App\Livewire\Cart;

use App\Cart\CartException;
use App\Cart\CartService;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AddToCart extends Component
{
    #[Locked]
    public Product $product;

    #[Validate('required|integer|min:1|max:'.CartService::MAX_QUANTITY, as: 'quantidade')]
    public $quantity = 1;

    public ?string $notice = null;

    public function add(): void
    {
        $this->notice = null;
        $this->validate();

        try {
            $this->cart()->add($this->product, (int) $this->quantity);
        } catch (CartException $e) {
            $this->addError('quantity', $e->getMessage());

            return;
        }

        $this->quantity = 1;
        $this->notice = 'Produto adicionado ao carrinho.';
        $this->dispatch('cart-updated');
    }

    public function render(): View
    {
        return view('livewire.cart.add-to-cart');
    }

    private function cart(): CartService
    {
        return app(CartService::class);
    }
}
