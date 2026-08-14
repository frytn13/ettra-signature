<?php

namespace App\Http\Requests\Admin;

use App\Models\DamagedGood;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDamagedGoodRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isInternalUser() ?? false; }
    public function rules(): array
    {
        return [
            'warehouse_stock_id' => ['required','integer','exists:warehouse_stocks,id'],
            'action' => ['required', Rule::in([DamagedGood::ACTION_MARK, DamagedGood::ACTION_RECOVER])],
            'quantity' => ['required','integer','min:1'],
            'reason' => ['required','string','max:100'],
            'notes' => ['nullable','string','max:1000'],
            'transaction_date' => ['required','date','before_or_equal:now'],
        ];
    }
}
