<?php

namespace App\Http\Requests\Admin;

use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->isInternalUser();
    }

    protected function prepareForValidation(): void
    {
        $warehouseStockId = $this->input('warehouse_stock_id');
        $physicalQuantity = $this->input('physical_quantity');

        $this->merge([
            'warehouse_stock_id' => $warehouseStockId === null || $warehouseStockId === '' ? null : (int) $warehouseStockId,
            'physical_quantity' => $physicalQuantity === null || $physicalQuantity === '' ? null : (int) $physicalQuantity,
            'reason' => strtolower(trim((string) $this->input('reason', ''))),
            'notes' => trim((string) $this->input('notes', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'warehouse_stock_id' => ['required', 'integer', Rule::exists('warehouse_stocks', 'id')],
            'physical_quantity' => ['required', 'integer', 'min:0', 'max:1000000000'],
            'reason' => ['required', Rule::in(array_keys(StockAdjustment::reasonOptions()))],
            'adjustment_date' => ['required', 'date', 'before_or_equal:now'],
            'notes' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'warehouse_stock_id.required' => 'Titik stok wajib dipilih.',
            'warehouse_stock_id.exists' => 'Titik stok tidak ditemukan.',
            'physical_quantity.required' => 'Stok fisik hasil pemeriksaan wajib diisi.',
            'physical_quantity.integer' => 'Stok fisik harus berupa angka bulat.',
            'physical_quantity.min' => 'Stok fisik tidak boleh bernilai negatif.',
            'reason.required' => 'Alasan penyesuaian wajib dipilih.',
            'reason.in' => 'Alasan penyesuaian tidak didukung.',
            'adjustment_date.required' => 'Tanggal dan waktu pemeriksaan wajib diisi.',
            'adjustment_date.before_or_equal' => 'Tanggal penyesuaian tidak boleh berada di masa mendatang.',
            'notes.required' => 'Catatan hasil pemeriksaan wajib diisi.',
            'notes.min' => 'Catatan penyesuaian minimal 5 karakter.',
        ];
    }
}
