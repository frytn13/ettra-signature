<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockTransferRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isInternalUser() ?? false; }
    public function rules(): array
    {
        return [
            'source_warehouse_id'=>['required','integer','exists:warehouses,id'],
            'destination_warehouse_id'=>['required','integer','different:source_warehouse_id','exists:warehouses,id'],
            'transfer_date'=>['required','date','before_or_equal:now'],
            'notes'=>['nullable','string','max:1000'],
            'items'=>['required','array','min:1','max:50'],
            'items.*.product_variant_id'=>['required','integer','distinct','exists:product_variants,id'],
            'items.*.quantity'=>['required','integer','min:1'],
        ];
    }
}
