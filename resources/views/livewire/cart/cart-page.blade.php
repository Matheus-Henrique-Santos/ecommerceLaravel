<section class="cart">
    @if ($lines->isEmpty())
        <p class="cart-empty">Seu carrinho está vazio.</p>
        <a class="admin-button" href="{{ route('site.shop') }}">Continuar comprando</a>
    @else
        <table class="cart-table">
            <thead>
                <tr>
                    <th scope="col">Produto</th>
                    <th scope="col">Preço</th>
                    <th scope="col">Quantidade</th>
                    <th scope="col">Subtotal</th>
                    <th scope="col"><span class="sr-only">Ações</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lines as $line)
                    <tr wire:key="cart-line-{{ $line->product->id }}">
                        <td class="cart-product">
                            <img src="{{ $line->product->image_url }}" alt="" width="64" height="64">
                            <a href="{{ route('site.product', $line->product->slug) }}">{{ $line->product->name }}</a>
                        </td>
                        <td>{{ $line->unitPrice()->format() }}</td>
                        <td>
                            <label>
                                <span class="sr-only">Quantidade de {{ $line->product->name }}</span>
                                <input type="number" min="0" max="{{ \App\Cart\CartService::MAX_QUANTITY }}"
                                       value="{{ $line->quantity }}"
                                       wire:change="updateQuantity({{ $line->product->id }}, $event.target.value)">
                            </label>
                            @error('quantity.'.$line->product->id)
                                <p class="form-error" role="alert">{{ $message }}</p>
                            @enderror
                            @if ($line->exceedsStock())
                                <p class="form-error">Estoque disponível: {{ $line->product->stock }}</p>
                            @endif
                        </td>
                        <td>{{ $line->subtotal()->format() }}</td>
                        <td>
                            <button type="button" class="cart-remove" wire:click="remove({{ $line->product->id }})">Remover</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <aside class="cart-summary">
            <h2>Resumo</h2>
            <p>Total <strong>{{ $total->format() }}</strong></p>
            <button type="button" class="cart-remove" wire:click="clear" wire:confirm="Esvaziar o carrinho?">Esvaziar carrinho</button>
        </aside>
    @endif
</section>
