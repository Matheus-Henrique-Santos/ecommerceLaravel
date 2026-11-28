<form class="add-to-cart" wire:submit="add">
    <label>
        <span class="sr-only">Quantidade</span>
        <input type="number" min="1" max="{{ \App\Cart\CartService::MAX_QUANTITY }}" wire:model="quantity" @disabled(! $product->isInStock())>
    </label>

    <button type="submit" wire:loading.attr="disabled" @disabled(! $product->isInStock())>
        Adicionar ao carrinho
    </button>

    @error('quantity')
        <p class="form-error" role="alert">{{ $message }}</p>
    @enderror

    @if ($notice)
        <p class="cart-notice" role="status">
            {{ $notice }} <a href="{{ route('site.cart') }}">Ver carrinho</a>
        </p>
    @endif
</form>
