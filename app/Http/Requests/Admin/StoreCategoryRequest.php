<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{
    /**
     * Owner dan Admin sama-sama boleh mengelola master kategori.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        return $user instanceof User && $user->isInternalUser();
    }

    /**
     * Menyeragamkan kode, nama, deskripsi, dan status sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        $description = trim((string) $this->input('description', ''));

        $this->merge([
            'code' => Str::upper(preg_replace('/\s+/', '', trim((string) $this->input('code', ''))) ?? ''),
            'name' => trim(preg_replace('/\s+/', ' ', (string) $this->input('name', '')) ?? ''),
            'description' => $description !== '' ? $description : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Aturan validasi kategori baru.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'min:2',
                'max:10',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('categories', 'code'),
            ],
            'name' => [
                'required',
                'string',
                'min:2',
                'max:100',
                Rule::unique('categories', 'name'),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * Pesan validasi berbahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Kode kategori wajib diisi.',
            'code.min' => 'Kode kategori minimal 2 karakter.',
            'code.max' => 'Kode kategori maksimal 10 karakter.',
            'code.regex' => 'Kode hanya boleh menggunakan huruf A-Z, angka, tanda hubung, atau garis bawah.',
            'code.unique' => 'Kode kategori tersebut sudah digunakan.',
            'name.required' => 'Nama kategori wajib diisi.',
            'name.min' => 'Nama kategori minimal 2 karakter.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'name.unique' => 'Nama kategori tersebut sudah digunakan.',
            'description.max' => 'Deskripsi kategori maksimal 1000 karakter.',
            'is_active.required' => 'Status kategori wajib ditentukan.',
        ];
    }
}
