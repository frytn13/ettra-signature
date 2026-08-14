<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateWarehouseStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->isInternalUser();
    }

    protected function prepareForValidation(): void
    {
        $minimumStock = $this->input('minimum_stock');

        $this->merge([
            'minimum_stock' => $minimumStock === null || $minimumStock === '' ? null : (int) $minimumStock,
        ]);
    }

    public function rules(): array
    {
        return [
            'minimum_stock' => ['required', 'integer', 'min:0', 'max:1000000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'minimum_stock.required' => 'Stok minimum wajib ditentukan.',
            'minimum_stock.integer' => 'Stok minimum harus berupa angka bulat.',
            'minimum_stock.min' => 'Stok minimum tidak boleh negatif.',
        ];
    }
}
