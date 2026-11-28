<?php

namespace App\Livewire\Cart;

use App\Cart\CartException;
use App\Cart\CartService;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CartPage extends Component
{
    public function updateQuantity(int $productId, mixed $quantity): void
    {
        $this->resetErrorBag();
        $errorKey = "quantity.{$productId}";

        if (filter_var($quantity, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => CartService::MAX_QUANTITY]]) === false) {
            $this->addError($errorKey, 'Informe uma quantidade entre 0 e '.CartService::MAX_QUANTITY.'.');

            return;
        }

        $product = Product::query()->find($productId);

        try {
            if ($product === null) {
                $this->cart()->remove($productId);
            } else {
                $this->cart()->update($product, (int) $quantity);
            }
        } catch (CartException $e) {
            $this->addError($errorKey, $e->getMessage());

            return;
        }

        $this->dispatch('cart-updated');
    }

    public function remove(int $productId): void
    {
        $this->cart()->remove($productId);
        $this->dispatch('cart-updated');
    }

    public function clear(): void
    {
        $this->cart()->clear();
        $this->dispatch('cart-updated');
    }

    public function render(): View
    {
        $lines = $this->cart()->lines();

        return view('livewire.cart.cart-page', [
            'lines' => $lines,
            'total' => CartService::sum($lines),
        ]);
    }

    private function cart(): CartService
    {
        return app(CartService::class);
    }
}
