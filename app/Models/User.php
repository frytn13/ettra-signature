<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'phone',
    'password',
    'account_type',
    'role',
    'is_active',
    'last_login_at',
    'last_login_ip',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public const ACCOUNT_INTERNAL = 'internal';

    public const ACCOUNT_CUSTOMER = 'customer';

    public const ROLE_OWNER = 'owner';

    public const ROLE_ADMIN = 'admin';

    /**
     * Daftar role internal yang diaktifkan pada tahap pengembangan saat ini.
     *
     * @return array<int, string>
     */
    public static function internalRoles(): array
    {
        return [
            self::ROLE_OWNER,
            self::ROLE_ADMIN,
        ];
    }

    /**
     * Memastikan akun merupakan pengguna internal yang memiliki role valid.
     */
    public function isInternalUser(): bool
    {
        return $this->account_type === self::ACCOUNT_INTERNAL
            && in_array($this->role, self::internalRoles(), true);
    }

    /**
     * Memeriksa apakah pengguna memiliki role tertentu.
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /**
     * Memeriksa apakah pengguna merupakan Owner.
     */
    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    /**
     * Memeriksa apakah pengguna merupakan Admin operasional.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Label role untuk ditampilkan pada antarmuka.
     */
    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_OWNER => 'Owner',
            self::ROLE_ADMIN => 'Admin',
            default => 'Pengguna Internal',
        };
    }

    /**
     * Hanya Owner yang boleh melihat data komersial sensitif sesuai kebutuhan sistem.
     */
    public function canViewSensitiveCommercialData(): bool
    {
        return $this->isOwner();
    }

    /**
     * Hanya Owner yang boleh mengelola vendor.
     */
    public function canManageVendors(): bool
    {
        return $this->isOwner();
    }

    /**
     * Hanya Owner yang boleh mengelola pengguna internal dan role.
     */
    public function canManageInternalUsers(): bool
    {
        return $this->isOwner();
    }

    /**
     * Hanya Owner yang melakukan pemeriksaan dan persetujuan purchase request.
     */
    public function canApprovePurchaseRequests(): bool
    {
        return $this->isOwner();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }
}
