<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Throwable;

class ActivityLogger
{
    /**
     * Kunci sensitif yang tidak boleh disimpan ke audit trail.
     *
     * @var array<int, string>
     */
    private const SENSITIVE_KEYS = [
        'password',
        'password_confirmation',
        'remember_token',
        'token',
        'secret',
    ];

    /**
     * Mencatat aktivitas tanpa membuat fitur utama gagal bila tabel log belum tersedia.
     */
    public function record(
        ?User $actor,
        string $action,
        string $module,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Request $request = null,
    ): ?ActivityLog {
        try {
            if (! Schema::hasTable('activity_logs')) {
                return null;
            }

            $request ??= app()->bound('request') ? request() : null;

            return ActivityLog::query()->create([
                'user_id' => $actor?->getKey(),
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'ip_address' => $request?->ip(),
                'user_agent' => $this->limitUserAgent($request?->userAgent()),
                'old_values' => $this->sanitize($oldValues),
                'new_values' => $this->sanitize($newValues),
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    /**
     * Menghapus informasi sensitif sebelum disimpan sebagai JSON audit.
     */
    private function sanitize(?array $values): ?array
    {
        if ($values === null || $values === []) {
            return null;
        }

        $sanitized = [];

        foreach ($values as $key => $value) {
            if (in_array((string) $key, self::SENSITIVE_KEYS, true)) {
                continue;
            }

            $sanitized[$key] = is_array($value)
                ? ($this->sanitize($value) ?? [])
                : $value;
        }

        return $sanitized === [] ? null : $sanitized;
    }

    /**
     * Membatasi panjang user agent supaya audit trail tetap ringan.
     */
    private function limitUserAgent(?string $userAgent): ?string
    {
        if ($userAgent === null || $userAgent === '') {
            return null;
        }

        return mb_substr($userAgent, 0, 1000);
    }
}
