<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public const ACTION_LOGIN = 'login';

    public const ACTION_LOGOUT = 'logout';

    public const ACTION_LOGIN_FAILED = 'login_failed';

    public const ACTION_CREATE = 'create';

    public const ACTION_UPDATE = 'update';

    public const ACTION_ACTIVATE = 'activate';

    public const ACTION_DEACTIVATE = 'deactivate';

    public const ACTION_DELETE = 'delete';

    public const ACTION_ROLE_CHANGE = 'role_change';

    public const ACTION_PASSWORD_CHANGE = 'password_change';

    public const ACTION_SHOW = 'show';

    public const ACTION_HIDE = 'hide';

    public const MODULE_AUTHENTICATION = 'authentication';

    public const MODULE_USER_MANAGEMENT = 'user_management';

    public const MODULE_CATEGORY_MANAGEMENT = 'category_management';

    public const MODULE_COLOR_MANAGEMENT = 'color_management';

    public const MODULE_SIZE_MANAGEMENT = 'size_management';

    public const MODULE_PRODUCT_MANAGEMENT = 'product_management';

    public const MODULE_PRODUCT_VARIANT_MANAGEMENT = 'product_variant_management';

    public const MODULE_WAREHOUSE_MANAGEMENT = 'warehouse_management';

    public const MODULE_WAREHOUSE_STOCK_MANAGEMENT = 'warehouse_stock_management';

    public const MODULE_STOCK_MOVEMENT_MANAGEMENT = 'stock_movement_management';

    public const MODULE_STOCK_ADJUSTMENT_MANAGEMENT = 'stock_adjustment_management';

    public const MODULE_DAMAGED_GOODS = 'damaged_goods';

    public const MODULE_STOCK_TRANSFER = 'stock_transfer';

    public const MODULE_SALES = 'sales';

    public const MODULE_PAYMENT = 'payment';

    public const MODULE_SHIPMENT = 'shipment';

    public const MODULE_PROMOTION = 'promotion';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'ip_address',
        'user_agent',
        'old_values',
        'new_values',
        'created_at',
    ];

    /**
     * Pengguna internal yang menjadi pelaku aktivitas.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    /**
     * Daftar aksi audit yang digunakan pada tahap saat ini.
     *
     * @return array<string, string>
     */
    public static function actionOptions(): array
    {
        return [
            self::ACTION_LOGIN => 'Login berhasil',
            self::ACTION_LOGOUT => 'Logout',
            self::ACTION_LOGIN_FAILED => 'Login gagal',
            self::ACTION_CREATE => 'Membuat data',
            self::ACTION_UPDATE => 'Mengubah data',
            self::ACTION_ACTIVATE => 'Mengaktifkan data',
            self::ACTION_DEACTIVATE => 'Menonaktifkan data',
            self::ACTION_DELETE => 'Menghapus data',
            self::ACTION_ROLE_CHANGE => 'Perubahan role',
            self::ACTION_PASSWORD_CHANGE => 'Perubahan password',
            self::ACTION_SHOW => 'Menampilkan data',
            self::ACTION_HIDE => 'Menyembunyikan data',
        ];
    }

    /**
     * Daftar modul audit yang tersedia saat ini.
     *
     * @return array<string, string>
     */
    public static function moduleOptions(): array
    {
        return [
            self::MODULE_AUTHENTICATION => 'Autentikasi',
            self::MODULE_USER_MANAGEMENT => 'User Management',
            self::MODULE_CATEGORY_MANAGEMENT => 'Kategori Produk',
            self::MODULE_COLOR_MANAGEMENT => 'Master Warna',
            self::MODULE_SIZE_MANAGEMENT => 'Master Ukuran',
            self::MODULE_PRODUCT_MANAGEMENT => 'Produk',
            self::MODULE_PRODUCT_VARIANT_MANAGEMENT => 'Variasi Produk',
            self::MODULE_WAREHOUSE_MANAGEMENT => 'Master Room',
            self::MODULE_WAREHOUSE_STOCK_MANAGEMENT => 'Stok Room',
            self::MODULE_STOCK_MOVEMENT_MANAGEMENT => 'Mutasi Stok',
            self::MODULE_STOCK_ADJUSTMENT_MANAGEMENT => 'Penyesuaian Stok',
            self::MODULE_DAMAGED_GOODS => 'Barang Rusak',
            self::MODULE_STOCK_TRANSFER => 'Transfer Room',
            self::MODULE_SALES => 'Penjualan',
            self::MODULE_PAYMENT => 'Pembayaran',
            self::MODULE_SHIPMENT => 'Pengiriman',
            self::MODULE_PROMOTION => 'Diskon & Promosi',
        ];
    }

    /**
     * Label aksi untuk antarmuka.
     */
    public function actionLabel(): string
    {
        return self::actionOptions()[$this->action] ?? str($this->action)->replace('_', ' ')->title()->toString();
    }

    /**
     * Label modul untuk antarmuka.
     */
    public function moduleLabel(): string
    {
        return self::moduleOptions()[$this->module] ?? str($this->module)->replace('_', ' ')->title()->toString();
    }

    /**
     * Tone visual berdasarkan jenis aktivitas.
     */
    public function tone(): string
    {
        return match ($this->action) {
            self::ACTION_LOGIN,
            self::ACTION_CREATE,
            self::ACTION_ACTIVATE,
            self::ACTION_SHOW => 'green',

            self::ACTION_LOGIN_FAILED,
            self::ACTION_DEACTIVATE,
            self::ACTION_DELETE,
            self::ACTION_HIDE => 'danger',

            default => 'peach',
        };
    }

    public function scopeAuthentication(Builder $query): Builder
    {
        return $query->where('module', self::MODULE_AUTHENTICATION);
    }

    public function scopeUserManagement(Builder $query): Builder
    {
        return $query->where('module', self::MODULE_USER_MANAGEMENT);
    }

    public function scopeCategoryManagement(Builder $query): Builder
    {
        return $query->where('module', self::MODULE_CATEGORY_MANAGEMENT);
    }

    public function scopeColorManagement(Builder $query): Builder
    {
        return $query->where('module', self::MODULE_COLOR_MANAGEMENT);
    }

    public function scopeSizeManagement(Builder $query): Builder
    {
        return $query->where('module', self::MODULE_SIZE_MANAGEMENT);
    }

    public function scopeProductManagement(Builder $query): Builder
    {
        return $query->where('module', self::MODULE_PRODUCT_MANAGEMENT);
    }

    public function scopeProductVariantManagement(Builder $query): Builder
    {
        return $query->where('module', self::MODULE_PRODUCT_VARIANT_MANAGEMENT);
    }

    public function scopeWarehouseManagement(Builder $query): Builder
    {
        return $query->where('module', self::MODULE_WAREHOUSE_MANAGEMENT);
    }

    public function scopeWarehouseStockManagement(Builder $query): Builder
    {
        return $query->where('module', self::MODULE_WAREHOUSE_STOCK_MANAGEMENT);
    }

    public function scopeStockMovementManagement(Builder $query): Builder
    {
        return $query->where('module', self::MODULE_STOCK_MOVEMENT_MANAGEMENT);
    }

    public function scopeStockAdjustmentManagement(Builder $query): Builder
    {
        return $query->where('module', self::MODULE_STOCK_ADJUSTMENT_MANAGEMENT);
    }

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
