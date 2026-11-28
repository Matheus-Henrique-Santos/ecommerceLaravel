<section class="catalog">
    <form class="catalog-filters" wire:submit.prevent role="search">
        <label>
            <span>Buscar</span>
            <input type="search" wire:model.live.debounce.300ms="search" placeholder="Nome ou SKU">
        </label>

        <label>
            <span>Categoria</span>
            <select wire:model.live="category">
                <option value="">Todas</option>
                @foreach ($categories as $option)
                    <option value="{{ $option->slug }}">{{ $option->name }}</option>
                @endforeach
            </select>
        </label>

        <label>
            <span>Ordenar por</span>
            <select wire:model.live="sort">
                @foreach ($sortOptions as $option)
                    <option value="{{ $option->value }}">{{ $option->label() }}</option>
                @endforeach
            </select>
        </label>

        @if ($search !== '' || $category !== '')
            <button type="button" wire:click="clearFilters">Limpar filtros</button>
        @endif
    </form>

    <p class="catalog-count" wire:loading.class="is-loading">
        {{ trans_choice('{0} Nenhum produto encontrado|{1} 1 produto encontrado|[2,*] :count produtos encontrados', $products->total(), ['count' => $products->total()]) }}
    </p>

    <div class="catalog-grid">
        @foreach ($products as $product)
            <article class="catalog-card" wire:key="product-{{ $product->id }}">
                <a href="{{ route('site.product', $product->slug) }}">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}">
                    <h2>{{ $product->name }}</h2>
                </a>
                <small>{{ $product->category->name }}</small>
                <strong>{{ $product->formatted_price }}</strong>
                @unless ($product->isInStock())
                    <span class="catalog-badge">Esgotado</span>
                @endunless
            </article>
        @endforeach
    </div>

    {{ $products->links() }}
</section>
