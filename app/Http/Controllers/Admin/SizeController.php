<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSizeRequest;
use App\Http\Requests\Admin\UpdateSizeRequest;
use App\Models\ActivityLog;
use App\Models\Size;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class SizeController extends Controller
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

        $sizes = Size::query()
            ->with(['createdBy:id,name', 'updatedBy:id,name'])
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] === 'active', fn (Builder $query): Builder => $query->active())
            ->when($filters['status'] === 'inactive', fn (Builder $query): Builder => $query->inactive())
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $statistics = [
            'total' => Size::query()->count(),
            'active' => Size::query()->active()->count(),
            'inactive' => Size::query()->inactive()->count(),
            'archived' => Size::onlyTrashed()->count(),
        ];

        return view('admin.sizes.index', compact('sizes', 'filters', 'statistics'));
    }

    public function create(): View
    {
        $nextSortOrder = max(1, (int) Size::query()->max('sort_order') + 1);

        return view('admin.sizes.create', [
            'size' => new Size([
                'sort_order' => $nextSortOrder,
                'is_active' => true,
            ]),
        ]);
    }

    public function store(StoreSizeRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();

        $size = Size::query()->create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'],
            'is_active' => $validated['is_active'],
            'created_by' => $actor?->getKey(),
            'updated_by' => $actor?->getKey(),
        ]);

        $this->activityLogger->record(
            $actor,
            ActivityLog::ACTION_CREATE,
            ActivityLog::MODULE_SIZE_MANAGEMENT,
            "Membuat ukuran {$size->code} - {$size->name}.",
            null,
            $this->auditableValues($size),
            $request,
        );

        return redirect()
            ->route('admin.sizes.index')
            ->with('success', "Ukuran {$size->name} berhasil ditambahkan.");
    }

    public function edit(Size $size): View
    {
        return view('admin.sizes.edit', compact('size'));
    }

    public function update(UpdateSizeRequest $request, Size $size): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();
        $oldValues = $this->auditableValues($size);
        $oldStatus = (bool) $size->is_active;

        $size->fill([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'],
            'is_active' => $validated['is_active'],
            'updated_by' => $actor?->getKey(),
        ])->save();

        $this->activityLogger->record(
            $actor,
            ActivityLog::ACTION_UPDATE,
            ActivityLog::MODULE_SIZE_MANAGEMENT,
            "Memperbarui ukuran {$size->code} - {$size->name}.",
            $oldValues,
            $this->auditableValues($size),
            $request,
        );

        if ($oldStatus !== (bool) $size->is_active) {
            $this->activityLogger->record(
                $actor,
                $size->is_active ? ActivityLog::ACTION_ACTIVATE : ActivityLog::ACTION_DEACTIVATE,
                ActivityLog::MODULE_SIZE_MANAGEMENT,
                $size->is_active
                    ? "Mengaktifkan ukuran {$size->code} - {$size->name}."
                    : "Menonaktifkan ukuran {$size->code} - {$size->name}.",
                ['is_active' => $oldStatus],
                ['is_active' => (bool) $size->is_active],
                $request,
            );
        }

        return redirect()
            ->route('admin.sizes.index')
            ->with('success', "Ukuran {$size->name} berhasil diperbarui.");
    }

    public function toggleStatus(Request $request, Size $size): RedirectResponse
    {
        $oldState = (bool) $size->is_active;
        $newState = ! $oldState;

        $size->forceFill([
            'is_active' => $newState,
            'updated_by' => $request->user()?->getKey(),
        ])->save();

        $this->activityLogger->record(
            $request->user(),
            $newState ? ActivityLog::ACTION_ACTIVATE : ActivityLog::ACTION_DEACTIVATE,
            ActivityLog::MODULE_SIZE_MANAGEMENT,
            $newState
                ? "Mengaktifkan ukuran {$size->code} - {$size->name}."
                : "Menonaktifkan ukuran {$size->code} - {$size->name}.",
            ['is_active' => $oldState],
            ['is_active' => $newState],
            $request,
        );

        return back()->with(
            'success',
            $newState
                ? "Ukuran {$size->name} berhasil diaktifkan."
                : "Ukuran {$size->name} berhasil dinonaktifkan."
        );
    }

    public function destroy(Request $request, Size $size): RedirectResponse
    {
        if ($this->isUsedByProductVariant($size)) {
            return back()->with(
                'error',
                "Ukuran {$size->name} sudah digunakan pada variasi produk dan tidak dapat dihapus. Nonaktifkan ukuran jika tidak ingin dipakai pada variasi baru."
            );
        }

        $oldValues = $this->auditableValues($size);
        $name = $size->name;
        $code = $size->code;

        $size->delete();

        $this->activityLogger->record(
            $request->user(),
            ActivityLog::ACTION_DELETE,
            ActivityLog::MODULE_SIZE_MANAGEMENT,
            "Menghapus ukuran {$code} - {$name} menggunakan soft delete.",
            $oldValues,
            ['deleted_at' => $size->deleted_at?->toDateTimeString()],
            $request,
        );

        return redirect()
            ->route('admin.sizes.index')
            ->with('success', "Ukuran {$name} berhasil dihapus dari daftar aktif.");
    }

    private function isUsedByProductVariant(Size $size): bool
    {
        if (! Schema::hasTable('product_variants') || ! Schema::hasColumn('product_variants', 'size_id')) {
            return false;
        }

        $query = DB::table('product_variants')->where('size_id', $size->getKey());

        if (Schema::hasColumn('product_variants', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return $query->exists();
    }

    /** @return array<string, mixed> */
    private function auditableValues(Size $size): array
    {
        return [
            'id' => $size->id,
            'code' => $size->code,
            'name' => $size->name,
            'sort_order' => $size->sort_order,
            'is_active' => (bool) $size->is_active,
        ];
    }
}
