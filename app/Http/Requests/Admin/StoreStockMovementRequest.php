<?php

namespace App\Http\Requests\Admin;

use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->isInternalUser();
    }

    protected function prepareForValidation(): void
    {
        $warehouseStockId = $this->input('warehouse_stock_id');
        $quantity = $this->input('quantity');

        $this->merge([
            'warehouse_stock_id' => $warehouseStockId === null || $warehouseStockId === '' ? null : (int) $warehouseStockId,
            'movement_type' => strtolower(trim((string) $this->input('movement_type', ''))),
            'quantity' => $quantity === null || $quantity === '' ? null : (int) $quantity,
            'notes' => trim((string) $this->input('notes', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'warehouse_stock_id' => ['required', 'integer', Rule::exists('warehouse_stocks', 'id')],
            'movement_type' => ['required', Rule::in(array_keys(StockMovement::manualTypeOptions()))],
            'quantity' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'movement_date' => ['required', 'date', 'before_or_equal:now'],
            'notes' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'warehouse_stock_id.required' => 'Titik stok wajib dipilih.',
            'warehouse_stock_id.exists' => 'Titik stok tidak ditemukan.',
            'movement_type.required' => 'Jenis mutasi wajib dipilih.',
            'movement_type.in' => 'Jenis mutasi yang dipilih tidak didukung.',
            'quantity.required' => 'Jumlah barang wajib diisi.',
            'quantity.integer' => 'Jumlah barang harus berupa angka bulat.',
            'quantity.min' => 'Jumlah barang minimal 1 unit.',
            'movement_date.required' => 'Tanggal dan waktu mutasi wajib diisi.',
            'movement_date.before_or_equal' => 'Tanggal mutasi tidak boleh berada di masa mendatang.',
            'notes.required' => 'Catatan/alasan mutasi wajib diisi agar histori dapat diaudit.',
            'notes.min' => 'Catatan mutasi minimal 5 karakter.',
        ];
    }
}
