<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->isInternalUser();
    }

    protected function prepareForValidation(): void
    {
        $code = Str::upper(preg_replace('/\s+/', '', trim((string) $this->input('code', ''))) ?? '');
        $name = trim(preg_replace('/\s+/', ' ', (string) $this->input('name', '')) ?? '');
        $address = trim((string) $this->input('address', ''));
        $description = trim((string) $this->input('description', ''));

        $this->merge([
            'code' => $code !== '' ? $code : null,
            'name' => $name,
            'address' => $address !== '' ? $address : null,
            'description' => $description !== '' ? $description : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        /** @var Warehouse|null $warehouse */
        $warehouse = $this->route('warehouse');

        return [
            'code' => [
                'nullable',
                'string',
                'min:2',
                'max:20',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('warehouses', 'code')->ignore($warehouse?->id),
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:150',
                Rule::unique('warehouses', 'name')->ignore($warehouse?->id),
            ],
            'address' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.min' => 'Kode room minimal 2 karakter.',
            'code.max' => 'Kode room maksimal 20 karakter.',
            'code.regex' => 'Kode room hanya boleh menggunakan huruf A-Z, angka, tanda hubung, atau garis bawah.',
            'code.unique' => 'Kode room tersebut sudah digunakan.',
            'name.required' => 'Nama room wajib diisi.',
            'name.min' => 'Nama room minimal 2 karakter.',
            'name.max' => 'Nama room maksimal 150 karakter.',
            'name.unique' => 'Nama room tersebut sudah digunakan.',
            'address.max' => 'Alamat room maksimal 2000 karakter.',
            'description.max' => 'Deskripsi room maksimal 1000 karakter.',
            'is_active.required' => 'Status room wajib ditentukan.',
        ];
    }
}
