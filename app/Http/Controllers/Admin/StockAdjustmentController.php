<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStockAdjustmentRequest;
use App\Models\ActivityLog;
use App\Models\StockAdjustment;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\ActivityLogger;
use App\Services\StockAdjustmentService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockAdjustmentController extends Controller
{
    public function __construct(
        private readonly StockAdjustmentService $stockAdjustmentService,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'warehouse' => (string) $request->query('warehouse', ''),
            'direction' => (string) $request->query('direction', ''),
            'reason' => (string) $request->query('reason', ''),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
        ];

        $adjustments = StockAdjustment::query()
            ->with([
                'warehouse:id,code,name',
                'productVariant:id,product_id,color_id,size_id,sku',
                'productVariant.product:id,code,name',
                'productVariant.color:id,code,name,hex_code',
                'productVariant.size:id,code,name',
                'processedBy:id,name,role',
                'stockMovement:id,transaction_number,movement_type,direction,quantity',
            ])
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('adjustment_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('stockMovement', fn (Builder $movementQuery): Builder => $movementQuery
                            ->where('transaction_number', 'like', "%{$search}%"))
                        ->orWhereHas('productVariant', fn (Builder $variantQuery): Builder => $variantQuery
                            ->where('sku', 'like', "%{$search}%")
                            ->orWhereHas('product', fn (Builder $productQuery): Builder => $productQuery
                                ->where('code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")))
                        ->orWhereHas('warehouse', fn (Builder $warehouseQuery): Builder => $warehouseQuery
                            ->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%"))
                        ->orWhereHas('processedBy', fn (Builder $userQuery): Builder => $userQuery
                            ->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['warehouse'] !== '', fn (Builder $query): Builder => $query->where('warehouse_id', (int) $filters['warehouse']))
            ->when($filters['direction'] === 'in', fn (Builder $query): Builder => $query->where('difference_quantity', '>', 0))
            ->when($filters['direction'] === 'out', fn (Builder $query): Builder => $query->where('difference_quantity', '<', 0))
            ->when($filters['reason'] !== '', fn (Builder $query): Builder => $query->where('reason', $filters['reason']))
            ->when($filters['date_from'] !== '', fn (Builder $query): Builder => $query->whereDate('adjustment_date', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn (Builder $query): Builder => $query->whereDate('adjustment_date', '<=', $filters['date_to']))
            ->orderByDesc('adjustment_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.stock-adjustments.index', [
            'adjustments' => $adjustments,
            'filters' => $filters,
            'statistics' => $this->statistics(),
            'warehouses' => Warehouse::query()->orderBy('name')->get(['id', 'code', 'name']),
            'reasons' => StockAdjustment::reasonOptions(),
        ]);
    }

    public function create(Request $request): View
    {
        $selectedStockId = $request->integer('stock') ?: null;

        $stocks = WarehouseStock::query()
            ->with([
                'warehouse:id,code,name,is_active',
                'productVariant:id,product_id,color_id,size_id,sku,is_active',
                'productVariant.product:id,code,name,status',
                'productVariant.color:id,code,name,hex_code',
                'productVariant.size:id,code,name',
            ])
            ->whereHas('warehouse', fn (Builder $query): Builder => $query->where('is_active', true))
            ->whereHas('productVariant', fn (Builder $query): Builder => $query->where('is_active', true))
            ->whereHas('productVariant.product', fn (Builder $query): Builder => $query->where('status', '!=', 'discontinued'))
            ->get()
            ->sortBy(fn (WarehouseStock $stock): string => ($stock->warehouse?->name ?? '').'|'.($stock->productVariant?->sku ?? ''))
            ->values();

        return view('admin.stock-adjustments.create', [
            'stocks' => $stocks,
            'selectedStockId' => $selectedStockId,
            'reasons' => StockAdjustment::reasonOptions(),
            'defaultAdjustmentDate' => now()->format('Y-m-d\TH:i'),
        ]);
    }

    public function store(StoreStockAdjustmentRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();
        $warehouseStock = WarehouseStock::query()->findOrFail($validated['warehouse_stock_id']);

        $adjustment = $this->stockAdjustmentService->process(
            $warehouseStock,
            (int) $validated['physical_quantity'],
            $validated['reason'],
            Carbon::parse($validated['adjustment_date']),
            $validated['notes'],
            $actor,
        );

        $adjustment->load([
            'warehouse:id,code,name',
            'productVariant:id,sku',
            'stockMovement:id,transaction_number',
        ]);

        $this->activityLogger->record(
            $actor,
            ActivityLog::ACTION_CREATE,
            ActivityLog::MODULE_STOCK_ADJUSTMENT_MANAGEMENT,
            "Memproses penyesuaian {$adjustment->adjustment_number} untuk {$adjustment->productVariant?->sku} di {$adjustment->warehouse?->code}. Stok fisik {$adjustment->system_quantity} menjadi {$adjustment->physical_quantity} unit.",
            [
                'quantity_on_hand' => (int) $adjustment->system_quantity,
            ],
            $this->auditableValues($adjustment),
            $request,
        );

        return redirect()
            ->route('admin.stock-adjustments.show', $adjustment)
            ->with('success', 'Penyesuaian stok berhasil diproses dan ledger mutasi sudah dibuat.');
    }

    public function show(StockAdjustment $stockAdjustment): View
    {
        $stockAdjustment->load([
            'warehouse:id,code,name,address',
            'warehouseStock:id,warehouse_id,product_variant_id,quantity_on_hand,quantity_reserved,quantity_damaged,minimum_stock',
            'productVariant:id,product_id,color_id,size_id,sku',
            'productVariant.product:id,category_id,code,name',
            'productVariant.product.category:id,code,name',
            'productVariant.color:id,code,name,hex_code',
            'productVariant.size:id,code,name',
            'processedBy:id,name,email,phone,role',
            'stockMovement:id,transaction_number,movement_type,direction,quantity,quantity_before,quantity_after,quantity_reserved_before,quantity_reserved_after,quantity_damaged_before,quantity_damaged_after,quantity_available_before,quantity_available_after,movement_date',
        ]);

        return view('admin.stock-adjustments.show', ['adjustment' => $stockAdjustment]);
    }

    /** @return array<string, int> */
    private function statistics(): array
    {
        return [
            'records' => StockAdjustment::query()->count(),
            'quantity_in' => (int) StockAdjustment::query()->where('difference_quantity', '>', 0)->sum('difference_quantity'),
            'quantity_out' => abs((int) StockAdjustment::query()->where('difference_quantity', '<', 0)->sum('difference_quantity')),
            'today' => StockAdjustment::query()->whereDate('adjustment_date', today())->count(),
        ];
    }

    /** @return array<string, mixed> */
    private function auditableValues(StockAdjustment $adjustment): array
    {
        return [
            'adjustment_number' => $adjustment->adjustment_number,
            'warehouse_id' => $adjustment->warehouse_id,
            'warehouse' => $adjustment->warehouse?->code.' - '.$adjustment->warehouse?->name,
            'product_variant_id' => $adjustment->product_variant_id,
            'sku' => $adjustment->productVariant?->sku,
            'system_quantity' => (int) $adjustment->system_quantity,
            'physical_quantity' => (int) $adjustment->physical_quantity,
            'difference_quantity' => (int) $adjustment->difference_quantity,
            'reason' => $adjustment->reason,
            'reason_label' => $adjustment->reasonLabel(),
            'status' => $adjustment->status,
            'stock_movement_number' => $adjustment->stockMovement?->transaction_number,
            'adjustment_date' => $adjustment->adjustment_date?->toDateTimeString(),
        ];
    }
}
