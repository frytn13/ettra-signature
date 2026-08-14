<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreInternalUserRequest extends FormRequest
{
    /**
     * Hanya Owner yang boleh membuat akun internal.
     */
    public function authorize(): bool
    {
        return $this->user()?->isOwner() ?? false;
    }

    /**
     * Normalisasi data sebelum validasi dijalankan.
     */
    protected function prepareForValidation(): void
    {
        $phone = $this->normalizePhone((string) $this->input('phone', ''));

        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'email' => Str::lower(trim((string) $this->input('email', ''))),
            'phone' => $phone !== '' ? $phone : null,
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    /**
     * Aturan validasi pembuatan pengguna internal.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'string', 'email:rfc', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'regex:/^08[0-9]{8,13}$/', Rule::unique('users', 'phone')],
            'role' => ['required', Rule::in(User::internalRoles())],
            'is_active' => ['required', 'boolean'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers(), 'max:72'],
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
            'name.required' => 'Nama pengguna wajib diisi.',
            'name.min' => 'Nama pengguna minimal 3 karakter.',
            'name.max' => 'Nama pengguna maksimal 100 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email tersebut sudah digunakan oleh akun lain.',
            'phone.regex' => 'Nomor telepon harus menggunakan format Indonesia yang valid, misalnya 081234567890.',
            'phone.unique' => 'Nomor telepon tersebut sudah digunakan oleh akun lain.',
            'role.required' => 'Role pengguna wajib dipilih.',
            'role.in' => 'Role yang dipilih tidak tersedia.',
            'is_active.required' => 'Status akun wajib dipilih.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.max' => 'Kata sandi maksimal 72 karakter.',
        ];
    }

    /**
     * Menormalisasi nomor telepon ke format 08xxxxxxxxxx.
     */
    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '62')) {
            return '0'.substr($digits, 2);
        }

        if (str_starts_with($digits, '8')) {
            return '0'.$digits;
        }

        return $digits;
    }
}
