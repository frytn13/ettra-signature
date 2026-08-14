<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreSalesReturnRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isInternalUser() ?? false; }

    public function rules(): array
    {
        return [
            'sales_order_id' => ['required', 'integer', 'exists:sales_orders,id'],
            'return_date' => ['required', 'date', 'before_or_equal:now'],
            'reason' => ['required', 'string', 'max:180'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'refund_requested' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.sales_order_item_id' => ['required', 'integer', 'exists:sales_order_items,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:0'],
            'items.*.condition' => ['required', 'in:sellable,damaged'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $hasQuantity = collect($this->input('items', []))->contains(fn ($item) => (int) ($item['quantity'] ?? 0) > 0);
            if (! $hasQuantity) $validator->errors()->add('items', 'Pilih minimal satu barang dengan jumlah retur lebih dari 0.');
        }];
    }
}
