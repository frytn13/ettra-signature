<?php

namespace App\Http\Requests\Admin;

use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->isInternalUser();
    }

    protected function prepareForValidation(): void
    {
        $sku = Str::upper(trim((string) $this->input('sku', '')));
        $sku = preg_replace('/\s+/', '-', $sku) ?? '';

        $this->merge([
            'sku' => $sku,
            'additional_price' => $this->input('additional_price', 0) === '' ? 0 : $this->input('additional_price', 0),
            'weight_grams' => $this->input('weight_grams') === '' ? null : $this->input('weight_grams'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        /** @var ProductVariant|null $productVariant */
        $productVariant = $this->route('productVariant');
        $productId = (int) $this->input('product_id');
        $colorId = (int) $this->input('color_id');
        $sizeId = (int) $this->input('size_id');

        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn (Builder $query): Builder => $query->whereNull('deleted_at')),
            ],
            'color_id' => [
                'required',
                'integer',
                Rule::exists('colors', 'id')->where(fn (Builder $query): Builder => $query->whereNull('deleted_at')),
            ],
            'size_id' => [
                'required',
                'integer',
                Rule::exists('sizes', 'id')->where(fn (Builder $query): Builder => $query->whereNull('deleted_at')),
                Rule::unique('product_variants')->where(
                    fn (Builder $query): Builder => $query
                        ->where('product_id', $productId)
                        ->where('color_id', $colorId)
                        ->where('size_id', $sizeId)
                )->ignore($productVariant?->id),
            ],
            'sku' => [
                'required',
                'string',
                'max:100',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('product_variants', 'sku')->ignore($productVariant?->id),
            ],
            'additional_price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'weight_grams' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak tersedia.',
            'color_id.required' => 'Warna wajib dipilih.',
            'color_id.exists' => 'Warna yang dipilih tidak tersedia.',
            'size_id.required' => 'Ukuran wajib dipilih.',
            'size_id.exists' => 'Ukuran yang dipilih tidak tersedia.',
            'size_id.unique' => 'Kombinasi produk, warna, dan ukuran tersebut sudah tersedia.',
            'sku.required' => 'SKU wajib diisi.',
            'sku.max' => 'SKU maksimal 100 karakter.',
            'sku.regex' => 'SKU hanya boleh menggunakan huruf A-Z, angka, tanda hubung, atau garis bawah.',
            'sku.unique' => 'SKU tersebut sudah digunakan oleh variasi lain.',
            'additional_price.required' => 'Tambahan harga wajib ditentukan. Isi 0 jika tidak ada tambahan.',
            'additional_price.numeric' => 'Tambahan harga harus berupa angka.',
            'additional_price.min' => 'Tambahan harga tidak boleh negatif.',
            'weight_grams.integer' => 'Berat variasi harus berupa angka bulat dalam gram.',
            'weight_grams.min' => 'Berat variasi minimal 1 gram.',
            'is_active.required' => 'Status variasi wajib ditentukan.',
        ];
    }
}
