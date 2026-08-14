<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStockMovementRequest;
use App\Models\ActivityLog;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\ActivityLogger;
use App\Services\StockMovementService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function __construct(
        private readonly StockMovementService $stockMovementService,
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'warehouse' => (string) $request->query('warehouse', ''),
            'type' => (string) $request->query('type', ''),
            'date_from' => (string) $request->query('date_from', ''),
            'date_to' => (string) $request->query('date_to', ''),
        ];

        $movements = StockMovement::query()
            ->with([
                'warehouse:id,code,name',
                'productVariant:id,product_id,color_id,size_id,sku',
                'productVariant.product:id,code,name',
                'productVariant.color:id,code,name,hex_code',
                'productVariant.size:id,code,name',
                'performedBy:id,name,role',
            ])
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('transaction_number', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('productVariant', fn (Builder $variantQuery): Builder => $variantQuery
                            ->where('sku', 'like', "%{$search}%")
                            ->orWhereHas('product', fn (Builder $productQuery): Builder => $productQuery
                                ->where('code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%")))
                        ->orWhereHas('warehouse', fn (Builder $warehouseQuery): Builder => $warehouseQuery
                            ->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%"))
                        ->orWhereHas('performedBy', fn (Builder $userQuery): Builder => $userQuery
                            ->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['warehouse'] !== '', fn (Builder $query): Builder => $query->where('warehouse_id', (int) $filters['warehouse']))
            ->when($filters['type'] !== '', fn (Builder $query): Builder => $query->where('movement_type', $filters['type']))
            ->when($filters['date_from'] !== '', fn (Builder $query): Builder => $query->whereDate('movement_date', '>=', $filters['date_from']))
            ->when($filters['date_to'] !== '', fn (Builder $query): Builder => $query->whereDate('movement_date', '<=', $filters['date_to']))
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.stock-movements.index', [
            'movements' => $movements,
            'filters' => $filters,
            'statistics' => $this->statistics(),
            'warehouses' => Warehouse::query()->orderBy('name')->get(['id', 'code', 'name']),
            'movementTypes' => StockMovement::typeOptions(),
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

        return view('admin.stock-movements.create', [
            'stocks' => $stocks,
            'selectedStockId' => $selectedStockId,
            'movementTypes' => StockMovement::manualTypeOptions(),
            'defaultMovementDate' => now()->format('Y-m-d\TH:i'),
        ]);
    }

    public function store(StoreStockMovementRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();
        $warehouseStock = WarehouseStock::query()->findOrFail($validated['warehouse_stock_id']);

        $movement = $this->stockMovementService->recordManualMovement(
            $warehouseStock,
            $validated['movement_type'],
            (int) $validated['quantity'],
            Carbon::parse($validated['movement_date']),
            $validated['notes'],
            $actor,
        );

        $movement->load([
            'warehouse:id,code,name',
            'productVariant:id,sku',
        ]);

        $this->activityLogger->record(
            $actor,
            ActivityLog::ACTION_CREATE,
            ActivityLog::MODULE_STOCK_MOVEMENT_MANAGEMENT,
            "Mencatat {$movement->typeLabel()} {$movement->quantity} unit untuk {$movement->productVariant?->sku} di {$movement->warehouse?->code} dengan nomor {$movement->transaction_number}.",
            null,
            $this->auditableValues($movement),
            $request,
        );

        return redirect()
            ->route('admin.stock-movements.show', $movement)
            ->with('success', 'Mutasi stok berhasil dicatat dan saldo stok sudah diperbarui.');
    }

    public function show(StockMovement $stockMovement): View
    {
        $stockMovement->load([
            'warehouse:id,code,name,address',
            'warehouseStock:id,warehouse_id,product_variant_id,minimum_stock',
            'productVariant:id,product_id,color_id,size_id,sku',
            'productVariant.product:id,category_id,code,name',
            'productVariant.product.category:id,code,name',
            'productVariant.color:id,code,name,hex_code',
            'productVariant.size:id,code,name',
            'performedBy:id,name,email,phone,role',
        ]);

        return view('admin.stock-movements.show', ['movement' => $stockMovement]);
    }

    /** @return array<string, int> */
    private function statistics(): array
    {
        return [
            'records' => StockMovement::query()->count(),
            'quantity_in' => (int) StockMovement::query()->where('direction', StockMovement::DIRECTION_IN)->sum('quantity'),
            'quantity_out' => (int) StockMovement::query()->where('direction', StockMovement::DIRECTION_OUT)->sum('quantity'),
            'today' => StockMovement::query()->whereDate('movement_date', today())->count(),
        ];
    }

    /** @return array<string, mixed> */
    private function auditableValues(StockMovement $movement): array
    {
        return [
            'transaction_number' => $movement->transaction_number,
            'warehouse_id' => $movement->warehouse_id,
            'warehouse' => $movement->warehouse?->code.' - '.$movement->warehouse?->name,
            'product_variant_id' => $movement->product_variant_id,
            'sku' => $movement->productVariant?->sku,
            'movement_type' => $movement->movement_type,
            'movement_type_label' => $movement->typeLabel(),
            'direction' => $movement->direction,
            'quantity' => (int) $movement->quantity,
            'quantity_before' => (int) $movement->quantity_before,
            'quantity_after' => (int) $movement->quantity_after,
            'quantity_available_before' => (int) $movement->quantity_available_before,
            'quantity_available_after' => (int) $movement->quantity_available_after,
            'movement_date' => $movement->movement_date?->toDateTimeString(),
        ];
    }
}
