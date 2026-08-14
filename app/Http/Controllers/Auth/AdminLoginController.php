<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    /**
     * Menampilkan halaman login pengguna internal.
     */
    public function create(): View
    {
        return view('auth.admin-login');
    }

    /**
     * Memproses autentikasi pengguna internal.
     *
     * Login dapat menggunakan email atau nomor telepon. Pada tahap ini hanya
     * role Owner dan Admin yang diizinkan masuk ke area administrasi.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ], [
            'login.required' => 'Email atau nomor telepon wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $login = trim($validated['login']);
        $throttleKey = $this->throttleKey($login, $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            $this->activityLogger->record(
                null,
                ActivityLog::ACTION_LOGIN_FAILED,
                ActivityLog::MODULE_AUTHENTICATION,
                'Percobaan login dibatasi karena terlalu banyak kegagalan dari identitas '.$this->maskLogin($login).'.',
                null,
                ['reason' => 'rate_limited', 'login' => $this->maskLogin($login)],
                $request,
            );

            throw ValidationException::withMessages([
                'login' => "Terlalu banyak percobaan login. Coba kembali dalam {$seconds} detik.",
            ]);
        }

        $user = $this->findInternalUser($login);

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            RateLimiter::hit($throttleKey, 60);

            $this->activityLogger->record(
                $user,
                ActivityLog::ACTION_LOGIN_FAILED,
                ActivityLog::MODULE_AUTHENTICATION,
                'Percobaan login gagal untuk identitas '.$this->maskLogin($login).'.',
                null,
                ['reason' => 'invalid_credentials', 'login' => $this->maskLogin($login)],
                $request,
            );

            throw ValidationException::withMessages([
                'login' => 'Email, nomor telepon, atau kata sandi yang Anda masukkan tidak sesuai.',
            ]);
        }

        if (! $user->is_active) {
            RateLimiter::hit($throttleKey, 60);

            $this->activityLogger->record(
                $user,
                ActivityLog::ACTION_LOGIN_FAILED,
                ActivityLog::MODULE_AUTHENTICATION,
                "Akun {$user->name} mencoba masuk ketika status akun sedang nonaktif.",
                null,
                ['reason' => 'inactive_account'],
                $request,
            );

            throw ValidationException::withMessages([
                'login' => 'Akun ini sedang tidak aktif. Hubungi Owner atau administrator sistem.',
            ]);
        }

        if (! $user->isInternalUser()) {
            RateLimiter::hit($throttleKey, 60);

            $this->activityLogger->record(
                $user,
                ActivityLog::ACTION_LOGIN_FAILED,
                ActivityLog::MODULE_AUTHENTICATION,
                "Akun {$user->name} ditolak karena tidak memiliki role internal yang diizinkan.",
                null,
                ['reason' => 'invalid_internal_role'],
                $request,
            );

            throw ValidationException::withMessages([
                'login' => 'Akun ini belum memiliki hak akses ke area administrasi.',
            ]);
        }

        Auth::login($user, (bool) ($validated['remember'] ?? false));
        $request->session()->regenerate();

        $previousLastLoginAt = $user->last_login_at?->toDateTimeString();
        $previousLastLoginIp = $user->last_login_ip;

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        $this->activityLogger->record(
            $user,
            ActivityLog::ACTION_LOGIN,
            ActivityLog::MODULE_AUTHENTICATION,
            "{$user->name} berhasil masuk sebagai {$user->roleLabel()}.",
            [
                'last_login_at' => $previousLastLoginAt,
                'last_login_ip' => $previousLastLoginIp,
            ],
            [
                'last_login_at' => $user->last_login_at?->toDateTimeString(),
                'last_login_ip' => $user->last_login_ip,
                'remember_me' => (bool) ($validated['remember'] ?? false),
            ],
            $request,
        );

        RateLimiter::clear($throttleKey);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Login berhasil.', 'redirect' => route('admin.dashboard')]);
        }

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Mengakhiri sesi pengguna internal.
     */
    public function destroy(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        $this->activityLogger->record(
            $user,
            ActivityLog::ACTION_LOGOUT,
            ActivityLog::MODULE_AUTHENTICATION,
            $user ? "{$user->name} keluar dari sistem." : 'Sesi pengguna internal diakhiri.',
            null,
            null,
            $request,
        );

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Anda telah keluar dari sistem dengan aman.', 'redirect' => route('admin.login')]);
        }

        return redirect()
            ->route('admin.login')
            ->with('status', 'Anda telah keluar dari sistem dengan aman.');
    }

    /**
     * Mencari akun internal berdasarkan email atau nomor telepon.
     */
    private function findInternalUser(string $login): ?User
    {
        $query = User::query()
            ->where('account_type', User::ACCOUNT_INTERNAL)
            ->whereIn('role', User::internalRoles());

        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            return $query
                ->whereRaw('LOWER(email) = ?', [Str::lower($login)])
                ->first();
        }

        $phone = $this->normalizePhone($login);

        if ($phone === '') {
            return null;
        }

        return $query
            ->where('phone', $phone)
            ->first();
    }

    /**
     * Menormalisasi nomor telepon ke bentuk 08xxxxxxxxxx untuk penyimpanan lokal.
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

    /**
     * Membuat key pembatas percobaan login berdasarkan identitas dan IP.
     */
    private function throttleKey(string $login, ?string $ip): string
    {
        return Str::transliterate(Str::lower($login).'|'.($ip ?? 'unknown'));
    }

    /**
     * Menyamarkan identitas login agar audit keamanan tidak menampilkan data penuh.
     */
    private function maskLogin(string $login): string
    {
        if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
            [$name, $domain] = array_pad(explode('@', $login, 2), 2, '');
            $visibleName = mb_substr($name, 0, min(2, mb_strlen($name)));

            return $visibleName.'***@'.$domain;
        }

        $digits = preg_replace('/\D+/', '', $login) ?? '';

        if (mb_strlen($digits) <= 4) {
            return '***';
        }

        return '***'.mb_substr($digits, -4);
    }
}
