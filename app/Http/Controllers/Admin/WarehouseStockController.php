<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWarehouseStockRequest;
use App\Http\Requests\Admin\UpdateWarehouseStockRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\ActivityLogger;
use App\Services\InventoryAnalysisService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class WarehouseStockController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
        private readonly InventoryAnalysisService $inventoryAnalysis,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'warehouse' => (string) $request->query('warehouse', ''),
            'category' => (string) $request->query('category', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $availableSql = WarehouseStock::availableSql();

        $stocks = WarehouseStock::query()
            ->with([
                'warehouse:id,code,name,is_active',
                'productVariant:id,product_id,color_id,size_id,sku,is_active',
                'productVariant.product:id,category_id,code,name,status',
                'productVariant.product.category:id,code,name',
                'productVariant.color:id,code,name,hex_code',
                'productVariant.size:id,code,name,sort_order',
                'updatedBy:id,name',
            ])
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->whereHas('productVariant', fn (Builder $variantQuery): Builder => $variantQuery
                            ->where('sku', 'like', "%{$search}%")
                            ->orWhereHas('product', fn (Builder $productQuery): Builder => $productQuery
                                ->where('code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")))
                        ->orWhereHas('warehouse', fn (Builder $warehouseQuery): Builder => $warehouseQuery
                            ->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['warehouse'] !== '', fn (Builder $query): Builder => $query->where('warehouse_id', (int) $filters['warehouse']))
            ->when($filters['category'] !== '', fn (Builder $query): Builder => $query->whereHas(
                'productVariant.product',
                fn (Builder $productQuery): Builder => $productQuery->where('category_id', (int) $filters['category'])
            ))
            ->when($filters['status'] === WarehouseStock::STATUS_SAFE, fn (Builder $query): Builder => $query
                ->whereRaw("({$availableSql}) > minimum_stock"))
            ->when($filters['status'] === WarehouseStock::STATUS_LOW, fn (Builder $query): Builder => $query
                ->whereRaw("({$availableSql}) > 0")
                ->whereRaw("({$availableSql}) <= minimum_stock"))
            ->when($filters['status'] === WarehouseStock::STATUS_OUT, fn (Builder $query): Builder => $query
                ->whereRaw("({$availableSql}) = 0"))
            ->orderByRaw("CASE WHEN ({$availableSql}) = 0 THEN 0 WHEN ({$availableSql}) <= minimum_stock THEN 1 ELSE 2 END")
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        $statistics = $this->statistics();
        $analysis = $this->inventoryAnalysis->analyze($filters['warehouse'] !== '' ? (int) $filters['warehouse'] : null);

        return view('admin.warehouse-stocks.index', [
            'stocks' => $stocks,
            'filters' => $filters,
            'statistics' => $statistics,
            'analysisSummary' => $analysis['summary'],
            'analysisRows' => $analysis['rows'],
            'warehouses' => Warehouse::query()->orderBy('name')->get(['id', 'code', 'name', 'is_active']),
            'categories' => Category::query()->orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function create(Request $request): View
    {
        $selectedWarehouseId = $request->integer('warehouse') ?: null;

        return view('admin.warehouse-stocks.create', [
            'selectedWarehouseId' => $selectedWarehouseId,
            'warehouses' => Warehouse::query()->active()->orderBy('name')->get(['id', 'code', 'name']),
            'variants' => ProductVariant::query()
                ->active()
                ->with([
                    'product:id,code,name,status',
                    'color:id,code,name,hex_code',
                    'size:id,code,name,sort_order',
                ])
                ->whereHas('product', fn (Builder $query): Builder => $query->where('status', '!=', 'discontinued'))
                ->orderBy('sku')
                ->get(),
        ]);
    }

    public function store(StoreWarehouseStockRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();
        $warehouse = Warehouse::query()->findOrFail($validated['warehouse_id']);
        $created = [];
        $skipped = 0;

        DB::transaction(function () use ($validated, $actor, $warehouse, &$created, &$skipped): void {
            $variants = ProductVariant::query()
                ->whereIn('id', $validated['product_variant_ids'])
                ->get()
                ->keyBy('id');

            foreach ($validated['product_variant_ids'] as $variantId) {
                $variant = $variants->get((int) $variantId);

                if (! $variant) {
                    continue;
                }

                $existing = WarehouseStock::query()
                    ->where('warehouse_id', $warehouse->id)
                    ->where('product_variant_id', $variant->id)
                    ->exists();

                if ($existing) {
                    $skipped++;
                    continue;
                }

                $stock = WarehouseStock::query()->create([
                    'warehouse_id' => $warehouse->id,
                    'product_variant_id' => $variant->id,
                    'quantity_on_hand' => 0,
                    'quantity_reserved' => 0,
                    'quantity_damaged' => 0,
                    'minimum_stock' => $validated['minimum_stock'],
                    'created_by' => $actor?->getKey(),
                    'updated_by' => $actor?->getKey(),
                ]);

                $created[] = [
                    'id' => $stock->id,
                    'product_variant_id' => $variant->id,
                    'sku' => $variant->sku,
                ];
            }
        });

        $this->activityLogger->record(
            $actor,
            ActivityLog::ACTION_CREATE,
            ActivityLog::MODULE_WAREHOUSE_STOCK_MANAGEMENT,
            "Mendaftarkan ".count($created)." SKU ke room {$warehouse->code} - {$warehouse->name}.",
            null,
            [
                'warehouse_id' => $warehouse->id,
                'warehouse' => $warehouse->code.' - '.$warehouse->name,
                'minimum_stock' => (int) $validated['minimum_stock'],
                'created_count' => count($created),
                'skipped_count' => $skipped,
                'stocks' => $created,
            ],
            $request,
        );

        if (count($created) === 0) {
            return redirect()
                ->route('admin.warehouse-stocks.index', ['warehouse' => $warehouse->id])
                ->with('error', 'Tidak ada SKU baru yang ditambahkan karena seluruh pilihan sudah terdaftar pada room tersebut.');
        }

        $message = count($created).' SKU berhasil didaftarkan pada '.$warehouse->name.'.';

        if ($skipped > 0) {
            $message .= " {$skipped} SKU dilewati karena sudah terdaftar.";
        }

        return redirect()
            ->route('admin.warehouse-stocks.index', ['warehouse' => $warehouse->id])
            ->with('success', $message);
    }

    public function show(WarehouseStock $warehouseStock): View
    {
        $warehouseStock->load([
            'warehouse:id,code,name,address,is_active',
            'productVariant:id,product_id,color_id,size_id,sku,is_active,additional_price,weight_grams',
            'productVariant.product:id,category_id,code,name,selling_price,weight_grams,status',
            'productVariant.product.category:id,code,name',
            'productVariant.color:id,code,name,hex_code',
            'productVariant.size:id,code,name,sort_order',
            'createdBy:id,name',
            'updatedBy:id,name',
        ]);

        return view('admin.warehouse-stocks.show', ['stock' => $warehouseStock]);
    }

    public function edit(WarehouseStock $warehouseStock): View
    {
        $warehouseStock->load([
            'warehouse:id,code,name,is_active',
            'productVariant:id,product_id,color_id,size_id,sku,is_active',
            'productVariant.product:id,code,name',
            'productVariant.color:id,code,name,hex_code',
            'productVariant.size:id,code,name',
        ]);

        return view('admin.warehouse-stocks.edit', ['stock' => $warehouseStock]);
    }

    public function update(UpdateWarehouseStockRequest $request, WarehouseStock $warehouseStock): RedirectResponse
    {
        $validated = $request->validated();
        $oldValues = $this->auditableValues($warehouseStock);

        $warehouseStock->forceFill([
            'minimum_stock' => $validated['minimum_stock'],
            'updated_by' => $request->user()?->getKey(),
        ])->save();

        $warehouseStock->refresh();

        $this->activityLogger->record(
            $request->user(),
            ActivityLog::ACTION_UPDATE,
            ActivityLog::MODULE_WAREHOUSE_STOCK_MANAGEMENT,
            "Mengubah stok minimum untuk {$warehouseStock->productVariant?->sku} pada {$warehouseStock->warehouse?->code}.",
            $oldValues,
            $this->auditableValues($warehouseStock),
            $request,
        );

        return redirect()
            ->route('admin.warehouse-stocks.show', $warehouseStock)
            ->with('success', 'Stok minimum berhasil diperbarui.');
    }

    public function destroy(Request $request, WarehouseStock $warehouseStock): RedirectResponse
    {
        if (
            $warehouseStock->quantity_on_hand > 0
            || $warehouseStock->quantity_reserved > 0
            || $warehouseStock->quantity_damaged > 0
            || $this->hasStockMovementHistory($warehouseStock)
        ) {
            return back()->with(
                'error',
                'Pencatatan stok ini tidak dapat dihapus karena sudah memiliki kuantitas atau histori pergerakan. Pertahankan data agar audit persediaan tetap utuh.'
            );
        }

        $oldValues = $this->auditableValues($warehouseStock);
        $sku = $warehouseStock->productVariant?->sku ?? '-';
        $warehouse = $warehouseStock->warehouse?->name ?? '-';
        $warehouseId = $warehouseStock->warehouse_id;

        $warehouseStock->delete();

        $this->activityLogger->record(
            $request->user(),
            ActivityLog::ACTION_DELETE,
            ActivityLog::MODULE_WAREHOUSE_STOCK_MANAGEMENT,
            "Menghapus pendaftaran stok {$sku} dari {$warehouse} karena belum memiliki kuantitas maupun histori pergerakan.",
            $oldValues,
            null,
            $request,
        );

        return redirect()
            ->route('admin.warehouse-stocks.index', ['warehouse' => $warehouseId])
            ->with('success', 'Pendaftaran SKU pada room berhasil dihapus.');
    }

    /** @return array<string, int> */
    private function statistics(): array
    {
        $availableSql = WarehouseStock::availableSql();

        return [
            'records' => WarehouseStock::query()->count(),
            'warehouses' => WarehouseStock::query()->distinct()->count('warehouse_id'),
            'on_hand' => (int) WarehouseStock::query()->sum('quantity_on_hand'),
            'reserved' => (int) WarehouseStock::query()->sum('quantity_reserved'),
            'damaged' => (int) WarehouseStock::query()->sum('quantity_damaged'),
            'available' => (int) (WarehouseStock::query()->selectRaw("COALESCE(SUM({$availableSql}), 0) AS total")->value('total') ?? 0),
            'low' => WarehouseStock::query()
                ->whereRaw("({$availableSql}) > 0")
                ->whereRaw("({$availableSql}) <= minimum_stock")
                ->count(),
            'out' => WarehouseStock::query()->whereRaw("({$availableSql}) = 0")->count(),
        ];
    }

    private function hasStockMovementHistory(WarehouseStock $warehouseStock): bool
    {
        if (! Schema::hasTable('stock_movements')) {
            return false;
        }

        if (Schema::hasColumn('stock_movements', 'warehouse_stock_id')) {
            return DB::table('stock_movements')->where('warehouse_stock_id', $warehouseStock->id)->exists();
        }

        if (
            Schema::hasColumn('stock_movements', 'warehouse_id')
            && Schema::hasColumn('stock_movements', 'product_variant_id')
        ) {
            return DB::table('stock_movements')
                ->where('warehouse_id', $warehouseStock->warehouse_id)
                ->where('product_variant_id', $warehouseStock->product_variant_id)
                ->exists();
        }

        return false;
    }

    /** @return array<string, mixed> */
    private function auditableValues(WarehouseStock $stock): array
    {
        $stock->loadMissing([
            'warehouse:id,code,name',
            'productVariant:id,sku',
        ]);

        return [
            'id' => $stock->id,
            'warehouse_id' => $stock->warehouse_id,
            'warehouse' => $stock->warehouse?->code.' - '.$stock->warehouse?->name,
            'product_variant_id' => $stock->product_variant_id,
            'sku' => $stock->productVariant?->sku,
            'quantity_on_hand' => (int) $stock->quantity_on_hand,
            'quantity_reserved' => (int) $stock->quantity_reserved,
            'quantity_damaged' => (int) $stock->quantity_damaged,
            'quantity_available' => $stock->availableQuantity(),
            'minimum_stock' => (int) $stock->minimum_stock,
            'status' => $stock->stockStatusLabel(),
        ];
    }
}
