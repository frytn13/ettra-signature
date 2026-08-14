<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWarehouseRequest;
use App\Http\Requests\Admin\UpdateWarehouseRequest;
use App\Models\ActivityLog;
use App\Models\Warehouse;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'status' => (string) $request->query('status', ''),
        ];

        $warehouses = Warehouse::query()
            ->with(['createdBy:id,name', 'updatedBy:id,name'])
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] === 'active', fn (Builder $query): Builder => $query->active())
            ->when($filters['status'] === 'inactive', fn (Builder $query): Builder => $query->inactive())
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $statistics = [
            'total' => Warehouse::query()->count(),
            'active' => Warehouse::query()->active()->count(),
            'inactive' => Warehouse::query()->inactive()->count(),
            'archived' => Warehouse::onlyTrashed()->count(),
        ];

        return view('admin.warehouses.index', compact('warehouses', 'filters', 'statistics'));
    }

    public function create(): View
    {
        return view('admin.warehouses.create', [
            'warehouse' => new Warehouse([
                'code' => $this->generateWarehouseCode(),
                'is_active' => true,
            ]),
        ]);
    }

    public function store(StoreWarehouseRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();
        $code = $validated['code'] ?: $this->generateWarehouseCode();

        $warehouse = Warehouse::query()->create([
            'code' => $code,
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'],
            'created_by' => $actor?->getKey(),
            'updated_by' => $actor?->getKey(),
        ]);

        $this->activityLogger->record(
            $actor,
            ActivityLog::ACTION_CREATE,
            ActivityLog::MODULE_WAREHOUSE_MANAGEMENT,
            "Membuat room {$warehouse->code} - {$warehouse->name}.",
            null,
            $this->auditableValues($warehouse),
            $request,
        );

        return redirect()
            ->route('admin.warehouses.show', $warehouse)
            ->with('success', "Room {$warehouse->name} berhasil ditambahkan.");
    }

    public function show(Warehouse $warehouse): View
    {
        $warehouse->load(['createdBy:id,name', 'updatedBy:id,name']);

        return view('admin.warehouses.show', [
            'warehouse' => $warehouse,
            'stockSummary' => $this->stockSummary($warehouse),
        ]);
    }

    public function edit(Warehouse $warehouse): View
    {
        return view('admin.warehouses.edit', compact('warehouse'));
    }

    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();
        $oldValues = $this->auditableValues($warehouse);
        $oldStatus = (bool) $warehouse->is_active;

        $warehouse->fill([
            'code' => $validated['code'] ?: $warehouse->code,
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'],
            'updated_by' => $actor?->getKey(),
        ])->save();

        $this->activityLogger->record(
            $actor,
            ActivityLog::ACTION_UPDATE,
            ActivityLog::MODULE_WAREHOUSE_MANAGEMENT,
            "Memperbarui room {$warehouse->code} - {$warehouse->name}.",
            $oldValues,
            $this->auditableValues($warehouse),
            $request,
        );

        if ($oldStatus !== (bool) $warehouse->is_active) {
            $this->activityLogger->record(
                $actor,
                $warehouse->is_active ? ActivityLog::ACTION_ACTIVATE : ActivityLog::ACTION_DEACTIVATE,
                ActivityLog::MODULE_WAREHOUSE_MANAGEMENT,
                $warehouse->is_active
                    ? "Mengaktifkan room {$warehouse->code} - {$warehouse->name}."
                    : "Menonaktifkan room {$warehouse->code} - {$warehouse->name}.",
                ['is_active' => $oldStatus],
                ['is_active' => (bool) $warehouse->is_active],
                $request,
            );
        }

        return redirect()
            ->route('admin.warehouses.show', $warehouse)
            ->with('success', "Room {$warehouse->name} berhasil diperbarui.");
    }

    public function toggleStatus(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $oldState = (bool) $warehouse->is_active;
        $newState = ! $oldState;

        $warehouse->forceFill([
            'is_active' => $newState,
            'updated_by' => $request->user()?->getKey(),
        ])->save();

        $this->activityLogger->record(
            $request->user(),
            $newState ? ActivityLog::ACTION_ACTIVATE : ActivityLog::ACTION_DEACTIVATE,
            ActivityLog::MODULE_WAREHOUSE_MANAGEMENT,
            $newState
                ? "Mengaktifkan room {$warehouse->code} - {$warehouse->name}."
                : "Menonaktifkan room {$warehouse->code} - {$warehouse->name}.",
            ['is_active' => $oldState],
            ['is_active' => $newState],
            $request,
        );

        return back()->with(
            'success',
            $newState
                ? "Room {$warehouse->name} berhasil diaktifkan."
                : "Room {$warehouse->name} berhasil dinonaktifkan."
        );
    }

    public function destroy(Request $request, Warehouse $warehouse): RedirectResponse
    {
        if ($this->hasOperationalReferences($warehouse)) {
            return back()->with(
                'error',
                "Room {$warehouse->name} sudah memiliki referensi stok atau transaksi dan tidak dapat dihapus. Nonaktifkan room untuk menghentikan penggunaannya tanpa merusak histori."
            );
        }

        $oldValues = $this->auditableValues($warehouse);
        $name = $warehouse->name;
        $code = $warehouse->code;

        $warehouse->delete();

        $this->activityLogger->record(
            $request->user(),
            ActivityLog::ACTION_DELETE,
            ActivityLog::MODULE_WAREHOUSE_MANAGEMENT,
            "Menghapus room {$code} - {$name} menggunakan soft delete.",
            $oldValues,
            ['deleted_at' => $warehouse->deleted_at?->toDateTimeString()],
            $request,
        );

        return redirect()
            ->route('admin.warehouses.index')
            ->with('success', "Room {$name} berhasil dihapus dari daftar aktif.");
    }

    private function generateWarehouseCode(): string
    {
        $codes = Warehouse::withTrashed()
            ->where('code', 'like', 'GD-%')
            ->pluck('code');

        $highest = 0;

        foreach ($codes as $code) {
            if (preg_match('/^GD-(\d+)$/', (string) $code, $matches) === 1) {
                $highest = max($highest, (int) $matches[1]);
            }
        }

        $next = $highest + 1;

        do {
            $candidate = sprintf('GD-%03d', $next);
            $exists = Warehouse::withTrashed()->where('code', $candidate)->exists();
            $next++;
        } while ($exists);

        return $candidate;
    }

    /** @return array<string, int> */
    private function stockSummary(Warehouse $warehouse): array
    {
        $summary = [
            'sku_count' => 0,
            'on_hand' => 0,
            'reserved' => 0,
            'available' => 0,
            'damaged' => 0,
        ];

        if (! Schema::hasTable('warehouse_stocks') || ! Schema::hasColumn('warehouse_stocks', 'warehouse_id')) {
            return $summary;
        }

        $query = DB::table('warehouse_stocks')->where('warehouse_id', $warehouse->getKey());
        $summary['sku_count'] = (int) (clone $query)->distinct()->count('product_variant_id');
        $summary['on_hand'] = (int) (clone $query)->sum('quantity_on_hand');
        $summary['reserved'] = (int) (clone $query)->sum('quantity_reserved');
        $summary['damaged'] = (int) (clone $query)->sum('quantity_damaged');
        $summary['available'] = max(0, $summary['on_hand'] - $summary['reserved'] - $summary['damaged']);

        return $summary;
    }

    private function hasOperationalReferences(Warehouse $warehouse): bool
    {
        $checks = [
            ['warehouse_stocks', ['warehouse_id']],
            ['stock_movements', ['warehouse_id', 'source_warehouse_id', 'destination_warehouse_id']],
            ['stock_transfers', ['source_warehouse_id', 'destination_warehouse_id', 'from_warehouse_id', 'to_warehouse_id']],
            ['stock_adjustments', ['warehouse_id']],
            ['goods_receipts', ['warehouse_id']],
        ];

        foreach ($checks as [$table, $columns]) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column) && DB::table($table)->where($column, $warehouse->getKey())->exists()) {
                    return true;
                }
            }
        }

        return false;
    }

    /** @return array<string, mixed> */
    private function auditableValues(Warehouse $warehouse): array
    {
        return [
            'id' => $warehouse->id,
            'code' => $warehouse->code,
            'name' => $warehouse->name,
            'address' => $warehouse->address,
            'description' => $warehouse->description,
            'is_active' => (bool) $warehouse->is_active,
        ];
    }
}
