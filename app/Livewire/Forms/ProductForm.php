<?php

namespace App\Livewire\Forms;

use App\Models\Product;
use App\Support\PriceInput;
use Closure;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\Form;
use Throwable;

/**
 * Back-office product form shared by the create and edit screens.
 *
 * Fields keep the raw text typed by the admin (price as "1.299,90", stock as
 * a string) and are only converted to domain values after validation.
 */
class ProductForm extends Form
{
    public ?Product $product = null;

    public string $categoryId = '';

    public string $name = '';

    public string $sku = '';

    public ?string $description = '';

    public string $price = '';

    public string $stock = '0';

    public bool $isActive = true;

    /** @var TemporaryUploadedFile|null */
    public $image = null;

    public function setProduct(Product $product): void
    {
        $this->product = $product;
        $this->categoryId = (string) $product->category_id;
        $this->name = $product->name;
        $this->sku = $product->sku;
        $this->description = (string) $product->description;
        $this->price = PriceInput::fromCents($product->price_cents);
        $this->stock = (string) $product->stock;
        $this->isActive = $product->is_active;
    }

    /**
     * Validates the input, stores the image (if any) and saves the product.
     *
     * A newly uploaded file is removed again if saving fails, and the previous
     * image is only deleted after the new one is persisted, so a failure
     * never leaves the product pointing to a missing file.
     */
    public function persist(): Product
    {
        $this->sku = Str::upper(trim($this->sku));
        $this->name = trim($this->name);

        $this->validate();

        $product = $this->product ?? new Product;
        $previousImage = $product->image_path;
        $disk = Storage::disk(Product::IMAGE_DISK);
        $newImage = $this->image?->store(Product::IMAGE_DIRECTORY, Product::IMAGE_DISK) ?: null;

        $product->fill([
            'category_id' => (int) $this->categoryId,
            'name' => $this->name,
            'sku' => $this->sku,
            'description' => trim((string) $this->description) ?: null,
            'price_cents' => PriceInput::toCents($this->price),
            'stock' => (int) $this->stock,
            'is_active' => $this->isActive,
        ]);

        if ($newImage !== null) {
            $product->image_path = $newImage;
        }

        try {
            $product->save();
        } catch (Throwable $exception) {
            if ($newImage !== null) {
                $disk->delete($newImage);
            }

            throw $exception;
        }

        if ($newImage !== null && $previousImage !== null) {
            $disk->delete($previousImage);
        }

        $this->product = $product;
        $this->image = null;

        return $product;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'categoryId' => ['bail', 'required', 'integer', Rule::exists('categories', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:255'],
            'sku' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Z0-9][A-Z0-9._-]*$/',
                // The unique index also covers trashed products, so they are not ignored here.
                Rule::unique('products', 'sku')->ignore($this->product?->getKey()),
            ],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => [
                'required',
                'string',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $cents = PriceInput::toCents((string) $value);

                    if ($cents === null || $cents < 1) {
                        $fail('Informe um preço válido, por exemplo 1.299,90.');
                    }
                },
            ],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'isActive' => ['boolean'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'categoryId' => 'categoria',
            'name' => 'nome',
            'sku' => 'SKU',
            'description' => 'descrição',
            'price' => 'preço',
            'stock' => 'estoque',
            'image' => 'imagem',
        ];
    }
}
