<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\DamagedGoodController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ProductAttributeController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\SalesOrderController;
use App\Http\Controllers\Admin\SalesReturnController;
use App\Http\Controllers\Admin\ShipmentController;
use App\Http\Controllers\Admin\SizeController;
use App\Http\Controllers\Admin\StockAdjustmentController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\StockTransferController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\WarehouseController;
use App\Http\Controllers\Admin\WarehouseStockController;
use App\Http\Controllers\Auth\AdminLoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('admin.dashboard') : redirect()->route('admin.login'));

Route::prefix('admin')->name('admin.')->middleware('no.cache')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('/login', [AdminLoginController::class, 'create'])->name('login');
        Route::post('/login', [AdminLoginController::class, 'store'])->name('login.store');
    });

    Route::middleware(['auth', 'role:owner,admin'])->group(function (): void {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Penjualan
        Route::prefix('sales')->name('sales.')->group(function (): void {
            Route::get('/', [SalesOrderController::class, 'index'])->name('index');
            Route::get('/create', [SalesOrderController::class, 'create'])->name('create');
            Route::post('/', [SalesOrderController::class, 'store'])->name('store');
            Route::patch('/{sale}/cancel', [SalesOrderController::class, 'cancel'])->name('cancel');
            Route::get('/{sale}/document/{type}', [SalesOrderController::class, 'document'])->name('document');
            Route::get('/{sale}', [SalesOrderController::class, 'show'])->name('show');
        });
        Route::prefix('sales-returns')->name('sales-returns.')->group(function (): void {
            Route::get('/', [SalesReturnController::class, 'index'])->name('index');
            Route::get('/create', [SalesReturnController::class, 'create'])->name('create');
            Route::post('/', [SalesReturnController::class, 'store'])->name('store');
            Route::get('/{salesReturn}', [SalesReturnController::class, 'show'])->name('show');
        });
        Route::prefix('payments')->name('payments.')->group(function (): void {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
            Route::get('/{payment}/proof', [PaymentController::class, 'proof'])->name('proof');
            Route::get('/{payment}/edit', [PaymentController::class, 'edit'])->name('edit');
            Route::put('/{payment}', [PaymentController::class, 'update'])->name('update');
        });
        Route::prefix('shipments')->name('shipments.')->group(function (): void {
            Route::get('/', [ShipmentController::class, 'index'])->name('index');
            Route::get('/{shipment}/edit', [ShipmentController::class, 'edit'])->name('edit');
            Route::put('/{shipment}', [ShipmentController::class, 'update'])->name('update');
        });
        Route::prefix('promotions')->name('promotions.')->group(function (): void {
            Route::get('/', [PromotionController::class, 'index'])->name('index');
            Route::get('/create', [PromotionController::class, 'create'])->name('create');
            Route::post('/', [PromotionController::class, 'store'])->name('store');
            Route::get('/{promotion}/edit', [PromotionController::class, 'edit'])->name('edit');
            Route::put('/{promotion}', [PromotionController::class, 'update'])->name('update');
            Route::patch('/{promotion}/status', [PromotionController::class, 'toggle'])->name('toggle');
            Route::delete('/{promotion}', [PromotionController::class, 'destroy'])->name('destroy');
        });

        // Produk & Persediaan
        Route::get('/product-attributes', [ProductAttributeController::class, 'index'])->name('product-attributes.index');

        Route::prefix('products')->name('products.')->group(function (): void {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::patch('/{product}/status', [ProductController::class, 'toggleStatus'])->name('toggle-status');
            Route::patch('/{product}/visibility', [ProductController::class, 'toggleVisibility'])->name('toggle-visibility');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
            Route::get('/{product}', [ProductController::class, 'show'])->name('show');
        });

        Route::prefix('product-variants')->name('product-variants.')->group(function (): void {
            Route::get('/', [ProductVariantController::class, 'index'])->name('index');
            Route::get('/create', [ProductVariantController::class, 'create'])->name('create');
            Route::post('/', [ProductVariantController::class, 'store'])->name('store');
            Route::get('/generate', [ProductVariantController::class, 'generateForm'])->name('generate-form');
            Route::post('/generate', [ProductVariantController::class, 'generate'])->name('generate');
            Route::get('/{productVariant}/edit', [ProductVariantController::class, 'edit'])->name('edit');
            Route::put('/{productVariant}', [ProductVariantController::class, 'update'])->name('update');
            Route::patch('/{productVariant}/status', [ProductVariantController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{productVariant}', [ProductVariantController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('categories')->name('categories.')->group(function (): void {
            Route::get('/', [CategoryController::class, 'index'])->name('index');
            Route::get('/create', [CategoryController::class, 'create'])->name('create');
            Route::post('/', [CategoryController::class, 'store'])->name('store');
            Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
            Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
            Route::patch('/{category}/status', [CategoryController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        });
        Route::prefix('colors')->name('colors.')->group(function (): void {
            Route::get('/', [ColorController::class, 'index'])->name('index');
            Route::get('/create', [ColorController::class, 'create'])->name('create');
            Route::post('/', [ColorController::class, 'store'])->name('store');
            Route::get('/{color}/edit', [ColorController::class, 'edit'])->name('edit');
            Route::put('/{color}', [ColorController::class, 'update'])->name('update');
            Route::patch('/{color}/status', [ColorController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{color}', [ColorController::class, 'destroy'])->name('destroy');
        });
        Route::prefix('sizes')->name('sizes.')->group(function (): void {
            Route::get('/', [SizeController::class, 'index'])->name('index');
            Route::get('/create', [SizeController::class, 'create'])->name('create');
            Route::post('/', [SizeController::class, 'store'])->name('store');
            Route::get('/{size}/edit', [SizeController::class, 'edit'])->name('edit');
            Route::put('/{size}', [SizeController::class, 'update'])->name('update');
            Route::patch('/{size}/status', [SizeController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{size}', [SizeController::class, 'destroy'])->name('destroy');
        });
        Route::prefix('warehouses')->name('warehouses.')->group(function (): void {
            Route::get('/', [WarehouseController::class, 'index'])->name('index');
            Route::get('/create', [WarehouseController::class, 'create'])->name('create');
            Route::post('/', [WarehouseController::class, 'store'])->name('store');
            Route::get('/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('edit');
            Route::put('/{warehouse}', [WarehouseController::class, 'update'])->name('update');
            Route::patch('/{warehouse}/status', [WarehouseController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{warehouse}', [WarehouseController::class, 'destroy'])->name('destroy');
            Route::get('/{warehouse}', [WarehouseController::class, 'show'])->name('show');
        });
        Route::prefix('warehouse-stocks')->name('warehouse-stocks.')->group(function (): void {
            Route::get('/', [WarehouseStockController::class, 'index'])->name('index');
            Route::get('/create', [WarehouseStockController::class, 'create'])->name('create');
            Route::post('/', [WarehouseStockController::class, 'store'])->name('store');
            Route::get('/{warehouseStock}/edit', [WarehouseStockController::class, 'edit'])->name('edit');
            Route::put('/{warehouseStock}', [WarehouseStockController::class, 'update'])->name('update');
            Route::delete('/{warehouseStock}', [WarehouseStockController::class, 'destroy'])->name('destroy');
            Route::get('/{warehouseStock}', [WarehouseStockController::class, 'show'])->name('show');
        });
        Route::prefix('stock-movements')->name('stock-movements.')->group(function (): void {
            Route::get('/', [StockMovementController::class, 'index'])->name('index');
            Route::get('/create', [StockMovementController::class, 'create'])->name('create');
            Route::post('/', [StockMovementController::class, 'store'])->name('store');
            Route::get('/{stockMovement}', [StockMovementController::class, 'show'])->name('show');
        });
        Route::prefix('stock-adjustments')->name('stock-adjustments.')->group(function (): void {
            Route::get('/', [StockAdjustmentController::class, 'index'])->name('index');
            Route::get('/create', [StockAdjustmentController::class, 'create'])->name('create');
            Route::post('/', [StockAdjustmentController::class, 'store'])->name('store');
            Route::get('/{stockAdjustment}', [StockAdjustmentController::class, 'show'])->name('show');
        });
        Route::prefix('damaged-goods')->name('damaged-goods.')->group(function (): void {
            Route::get('/', [DamagedGoodController::class, 'index'])->name('index');
            Route::get('/create', [DamagedGoodController::class, 'create'])->name('create');
            Route::post('/', [DamagedGoodController::class, 'store'])->name('store');
            Route::get('/{damagedGood}', [DamagedGoodController::class, 'show'])->name('show');
        });
        Route::prefix('stock-transfers')->name('stock-transfers.')->group(function (): void {
            Route::get('/', [StockTransferController::class, 'index'])->name('index');
            Route::get('/create', [StockTransferController::class, 'create'])->name('create');
            Route::post('/', [StockTransferController::class, 'store'])->name('store');
            Route::get('/{stockTransfer}', [StockTransferController::class, 'show'])->name('show');
        });

        Route::post('/logout', [AdminLoginController::class, 'destroy'])->name('logout');
    });

    Route::middleware(['auth', 'role:owner'])->group(function (): void {
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::prefix('users')->name('users.')->group(function (): void {
            Route::get('/', [UserManagementController::class, 'index'])->name('index');
            Route::get('/create', [UserManagementController::class, 'create'])->name('create');
            Route::post('/', [UserManagementController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
            Route::patch('/{user}/status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
            Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
        });
    });
});
