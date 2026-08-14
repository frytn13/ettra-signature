<?php

namespace App\Http\Requests\Admin;

use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateSizeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->isInternalUser();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => Str::upper(preg_replace('/\s+/', '', trim((string) $this->input('code', ''))) ?? ''),
            'name' => trim(preg_replace('/\s+/', ' ', (string) $this->input('name', '')) ?? ''),
            'sort_order' => (int) $this->input('sort_order', 1),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        /** @var Size|null $size */
        $size = $this->route('size');

        return [
            'code' => [
                'required',
                'string',
                'min:1',
                'max:20',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('sizes', 'code')->ignore($size?->id),
            ],
            'name' => [
                'required',
                'string',
                'min:1',
                'max:80',
                Rule::unique('sizes', 'name')->ignore($size?->id),
            ],
            'sort_order' => ['required', 'integer', 'min:1', 'max:999'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode ukuran wajib diisi.',
            'code.max' => 'Kode ukuran maksimal 20 karakter.',
            'code.regex' => 'Kode hanya boleh menggunakan huruf A-Z, angka, tanda hubung, atau garis bawah.',
            'code.unique' => 'Kode ukuran tersebut sudah digunakan.',
            'name.required' => 'Nama ukuran wajib diisi.',
            'name.max' => 'Nama ukuran maksimal 80 karakter.',
            'name.unique' => 'Nama ukuran tersebut sudah digunakan.',
            'sort_order.required' => 'Urutan tampilan wajib diisi.',
            'sort_order.integer' => 'Urutan tampilan harus berupa angka bulat.',
            'sort_order.min' => 'Urutan tampilan minimal 1.',
            'sort_order.max' => 'Urutan tampilan maksimal 999.',
            'is_active.required' => 'Status ukuran wajib ditentukan.',
        ];
    }
}
