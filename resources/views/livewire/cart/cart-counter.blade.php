<a class="cart-counter" href="{{ route('site.cart') }}" aria-label="Carrinho com {{ $count }} {{ $count === 1 ? 'item' : 'itens' }}">
    <img src="{{ asset('img/navbar/shopping_cart.svg') }}" alt="">
    @if ($count > 0)
        <span class="cart-counter-badge">{{ $count }}</span>
    @endif
</a>
