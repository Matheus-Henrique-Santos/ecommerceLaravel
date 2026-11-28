<form wire:submit="save" class="admin-form" novalidate>
    <label>
        Nome
        <input type="text" wire:model="form.name" maxlength="255" required>
    </label>
    @error('form.name') <p class="form-error">{{ $message }}</p> @enderror

    <label>
        Categoria
        <select wire:model="form.categoryId" required>
            <option value="">Selecione…</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">
                    {{ $category->name }}@unless ($category->is_active) (inativa)@endunless
                </option>
            @endforeach
        </select>
    </label>
    @error('form.categoryId') <p class="form-error">{{ $message }}</p> @enderror

    <div class="admin-form-row">
        <label>
            SKU
            <input type="text" wire:model="form.sku" maxlength="64" required>
        </label>

        <label>
            Preço (R$)
            <input type="text" inputmode="decimal" wire:model="form.price" placeholder="1.299,90" required>
        </label>

        <label>
            Estoque
            <input type="number" min="0" step="1" wire:model="form.stock" required>
        </label>
    </div>
    @error('form.sku') <p class="form-error">{{ $message }}</p> @enderror
    @error('form.price') <p class="form-error">{{ $message }}</p> @enderror
    @error('form.stock') <p class="form-error">{{ $message }}</p> @enderror

    <label>
        Descrição
        <textarea wire:model="form.description" rows="5" maxlength="5000"></textarea>
    </label>
    @error('form.description') <p class="form-error">{{ $message }}</p> @enderror

    <label>
        Imagem (JPG, PNG ou WebP, até 2 MB)
        <input type="file" wire:model="form.image" accept="image/jpeg,image/png,image/webp">
    </label>
    <p wire:loading wire:target="form.image">Enviando imagem…</p>
    @error('form.image') <p class="form-error">{{ $message }}</p> @enderror

    @if ($form->product?->image_path)
        <figure class="admin-current-image">
            <img src="{{ $form->product->image_url }}" alt="Imagem atual de {{ $form->product->name }}" width="160">
            <figcaption>Imagem atual — envie outra para substituir.</figcaption>
        </figure>
    @endif

    <label class="checkbox">
        <input type="checkbox" wire:model="form.isActive">
        Visível na loja
    </label>

    <div class="admin-form-actions">
        <button type="submit" wire:loading.attr="disabled">Salvar</button>
        <a href="{{ route('admin.products.index') }}">Cancelar</a>
    </div>
</form>
