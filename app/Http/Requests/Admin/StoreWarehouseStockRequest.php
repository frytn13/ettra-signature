<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->isInternalUser();
    }

    protected function prepareForValidation(): void
    {
        $warehouseId = $this->input('warehouse_id');
        $minimumStock = $this->input('minimum_stock');

        $this->merge([
            'warehouse_id' => $warehouseId === null || $warehouseId === '' ? null : (int) $warehouseId,
            'minimum_stock' => $minimumStock === null || $minimumStock === '' ? null : (int) $minimumStock,
        ]);
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->whereNull('deleted_at')),
            ],
            'product_variant_ids' => ['required', 'array', 'min:1', 'max:100'],
            'product_variant_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('product_variants', 'id')->where(fn ($query) => $query
                    ->where('is_active', true)
                    ->whereNull('deleted_at')),
            ],
            'minimum_stock' => ['required', 'integer', 'min:0', 'max:1000000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'Room wajib dipilih.',
            'warehouse_id.exists' => 'Room tidak tersedia atau sedang nonaktif.',
            'product_variant_ids.required' => 'Pilih minimal satu variasi produk.',
            'product_variant_ids.min' => 'Pilih minimal satu variasi produk.',
            'product_variant_ids.max' => 'Maksimal 100 variasi dapat didaftarkan dalam satu proses.',
            'product_variant_ids.*.exists' => 'Salah satu variasi tidak tersedia atau sedang nonaktif.',
            'minimum_stock.required' => 'Stok minimum wajib ditentukan.',
            'minimum_stock.integer' => 'Stok minimum harus berupa angka bulat.',
            'minimum_stock.min' => 'Stok minimum tidak boleh negatif.',
        ];
    }
}
