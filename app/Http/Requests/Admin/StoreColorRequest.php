<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreColorRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->isInternalUser();
    }

    protected function prepareForValidation(): void
    {
        $hexCode = strtoupper(trim((string) $this->input('hex_code', '')));

        if ($hexCode !== '' && ! str_starts_with($hexCode, '#')) {
            $hexCode = '#'.$hexCode;
        }

        $this->merge([
            'code' => Str::upper(preg_replace('/\s+/', '', trim((string) $this->input('code', ''))) ?? ''),
            'name' => trim(preg_replace('/\s+/', ' ', (string) $this->input('name', '')) ?? ''),
            'hex_code' => $hexCode !== '' ? $hexCode : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'min:1',
                'max:12',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('colors', 'code'),
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:80',
                Rule::unique('colors', 'name'),
            ],
            'hex_code' => [
                'nullable',
                'string',
                'regex:/^#[0-9A-F]{6}$/',
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode warna wajib diisi.',
            'code.max' => 'Kode warna maksimal 12 karakter.',
            'code.regex' => 'Kode hanya boleh menggunakan huruf A-Z, angka, tanda hubung, atau garis bawah.',
            'code.unique' => 'Kode warna tersebut sudah digunakan.',
            'name.required' => 'Nama warna wajib diisi.',
            'name.min' => 'Nama warna minimal 2 karakter.',
            'name.max' => 'Nama warna maksimal 80 karakter.',
            'name.unique' => 'Nama warna tersebut sudah digunakan.',
            'hex_code.regex' => 'Kode HEX harus menggunakan format #RRGGBB, misalnya #BB7F73.',
            'is_active.required' => 'Status warna wajib ditentukan.',
        ];
    }
}
