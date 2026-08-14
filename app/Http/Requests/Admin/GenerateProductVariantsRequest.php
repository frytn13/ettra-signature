<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GenerateProductVariantsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->isInternalUser();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'additional_price' => $this->input('additional_price', 0) === '' ? 0 : $this->input('additional_price', 0),
            'weight_grams' => $this->input('weight_grams') === '' ? null : $this->input('weight_grams'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id')->where(fn (Builder $query): Builder => $query->whereNull('deleted_at')),
            ],
            'color_ids' => ['required', 'array', 'min:1', 'max:10'],
            'color_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('colors', 'id')->where(fn (Builder $query): Builder => $query->whereNull('deleted_at')->where('is_active', true)),
            ],
            'size_ids' => ['required', 'array', 'min:1', 'max:10'],
            'size_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('sizes', 'id')->where(fn (Builder $query): Builder => $query->whereNull('deleted_at')->where('is_active', true)),
            ],
            'additional_price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
            'weight_grams' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $colorCount = count((array) $this->input('color_ids', []));
                $sizeCount = count((array) $this->input('size_ids', []));

                if (($colorCount * $sizeCount) > 100) {
                    $validator->errors()->add('color_ids', 'Maksimal 100 kombinasi dapat dibuat dalam satu proses.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Produk wajib dipilih.',
            'product_id.exists' => 'Produk yang dipilih tidak tersedia.',
            'color_ids.required' => 'Pilih minimal satu warna.',
            'color_ids.min' => 'Pilih minimal satu warna.',
            'color_ids.max' => 'Maksimal 10 warna dapat dipilih dalam satu proses.',
            'color_ids.*.exists' => 'Salah satu warna tidak tersedia atau sedang nonaktif.',
            'size_ids.required' => 'Pilih minimal satu ukuran.',
            'size_ids.min' => 'Pilih minimal satu ukuran.',
            'size_ids.max' => 'Maksimal 10 ukuran dapat dipilih dalam satu proses.',
            'size_ids.*.exists' => 'Salah satu ukuran tidak tersedia atau sedang nonaktif.',
            'additional_price.min' => 'Tambahan harga tidak boleh negatif.',
            'weight_grams.integer' => 'Berat variasi harus berupa angka bulat dalam gram.',
            'weight_grams.min' => 'Berat variasi minimal 1 gram.',
        ];
    }
}
