<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->isInternalUser();
    }

    protected function prepareForValidation(): void
    {
        $code = trim((string) $this->input('code', ''));
        $description = trim((string) $this->input('description', ''));

        $this->merge([
            'code' => $code !== ''
                ? Str::upper(preg_replace('/\s+/', '', $code) ?? '')
                : null,
            'name' => trim(preg_replace('/\s+/', ' ', (string) $this->input('name', '')) ?? ''),
            'description' => $description !== '' ? $description : null,
            'is_visible' => $this->boolean('is_visible'),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $isOwner = $this->user()?->isOwner() === true;

        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->whereNull('deleted_at')->where('is_active', true)),
            ],
            'code' => [
                'nullable',
                'string',
                'max:40',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('products', 'code'),
            ],
            'name' => ['required', 'string', 'min:2', 'max:180'],
            'description' => ['nullable', 'string', 'max:5000'],
            'initial_purchase_price' => $isOwner
                ? ['nullable', 'numeric', 'min:0', 'max:999999999999.99']
                : ['prohibited'],
            'cost_price' => $isOwner
                ? ['nullable', 'numeric', 'min:0', 'max:999999999999.99']
                : ['prohibited'],
            'selling_price' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'status' => ['required', Rule::in(array_keys(Product::statusOptions()))],
            'availability_status' => ['required', Rule::in(array_keys(Product::availabilityOptions()))],
            'is_visible' => ['required', 'boolean'],
            'weight_grams' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'entry_date' => ['nullable', 'date', 'before_or_equal:today'],
            'primary_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'additional_images' => ['nullable', 'array', 'max:6'],
            'additional_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori produk wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak tersedia atau sedang nonaktif.',
            'code.max' => 'Kode produk maksimal 40 karakter.',
            'code.regex' => 'Kode produk hanya boleh menggunakan huruf A-Z, angka, tanda hubung, atau garis bawah.',
            'code.unique' => 'Kode produk tersebut sudah digunakan.',
            'name.required' => 'Nama produk wajib diisi.',
            'name.min' => 'Nama produk minimal 2 karakter.',
            'name.max' => 'Nama produk maksimal 180 karakter.',
            'description.max' => 'Deskripsi produk maksimal 5000 karakter.',
            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.min' => 'Harga jual harus lebih besar dari Rp0.',
            'initial_purchase_price.prohibited' => 'Harga beli awal hanya dapat dikelola oleh Owner.',
            'cost_price.prohibited' => 'Harga modal hanya dapat dikelola oleh Owner.',
            'weight_grams.integer' => 'Berat produk harus berupa angka bulat dalam gram.',
            'entry_date.before_or_equal' => 'Tanggal produk masuk tidak boleh melebihi hari ini.',
            'primary_image.image' => 'Foto utama harus berupa file gambar.',
            'primary_image.mimes' => 'Foto utama harus berformat JPG, JPEG, PNG, atau WebP.',
            'primary_image.max' => 'Ukuran foto utama maksimal 5 MB.',
            'additional_images.max' => 'Maksimal 6 foto tambahan dapat diunggah sekaligus.',
            'additional_images.*.image' => 'Setiap foto tambahan harus berupa file gambar.',
            'additional_images.*.mimes' => 'Foto tambahan harus berformat JPG, JPEG, PNG, atau WebP.',
            'additional_images.*.max' => 'Ukuran setiap foto tambahan maksimal 5 MB.',
        ];
    }
}
