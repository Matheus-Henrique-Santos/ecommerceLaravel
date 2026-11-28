<section class="admin-products">
    <div class="admin-toolbar">
        <input type="search" wire:model.live.debounce.300ms="search" placeholder="Buscar por nome ou SKU" aria-label="Buscar produtos">

        <select wire:model.live="status" aria-label="Filtrar por status">
            <option value="">Todos</option>
            <option value="ativos">Ativos</option>
            <option value="inativos">Inativos</option>
        </select>

        <a class="admin-button" href="{{ route('admin.products.create') }}">Novo produto</a>
    </div>

    <table class="admin-table">
        <thead>
            <tr>
                <th scope="col">Produto</th>
                <th scope="col">SKU</th>
                <th scope="col">Categoria</th>
                <th scope="col">Preço</th>
                <th scope="col">Estoque</th>
                <th scope="col">Status</th>
                <th scope="col"><span class="sr-only">Ações</span></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($products as $product)
                <tr wire:key="admin-product-{{ $product->id }}">
                    <td class="admin-product-name">
                        <img src="{{ $product->image_url }}" alt="" width="40" height="40">
                        {{ $product->name }}
                    </td>
                    <td>{{ $product->sku }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td>{{ $product->formatted_price }}</td>
                    <td>{{ $product->stock }}</td>
                    <td>
                        <span @class(['admin-status', 'is-inactive' => ! $product->is_active])>
                            {{ $product->is_active ? 'Ativo' : 'Inativo' }}
                        </span>
                    </td>
                    <td class="admin-actions">
                        <a href="{{ route('admin.products.edit', $product) }}">Editar</a>
                        <button type="button" wire:click="toggleActive({{ $product->id }})">
                            {{ $product->is_active ? 'Desativar' : 'Ativar' }}
                        </button>
                        <button type="button" wire:click="delete({{ $product->id }})" wire:confirm="Remover &quot;{{ $product->name }}&quot; do catálogo?">
                            Remover
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Nenhum produto encontrado.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $products->links() }}
</section>
