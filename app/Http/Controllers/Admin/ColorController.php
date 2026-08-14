<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreColorRequest;
use App\Http\Requests\Admin\UpdateColorRequest;
use App\Models\ActivityLog;
use App\Models\Color;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ColorController extends Controller
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

        $colors = Color::query()
            ->with(['createdBy:id,name', 'updatedBy:id,name'])
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('hex_code', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] === 'active', fn (Builder $query): Builder => $query->active())
            ->when($filters['status'] === 'inactive', fn (Builder $query): Builder => $query->inactive())
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $statistics = [
            'total' => Color::query()->count(),
            'active' => Color::query()->active()->count(),
            'inactive' => Color::query()->inactive()->count(),
            'archived' => Color::onlyTrashed()->count(),
        ];

        return view('admin.colors.index', compact('colors', 'filters', 'statistics'));
    }

    public function create(): View
    {
        return view('admin.colors.create', [
            'color' => new Color(['is_active' => true]),
        ]);
    }

    public function store(StoreColorRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();

        $color = Color::query()->create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'hex_code' => $validated['hex_code'] ?? null,
            'is_active' => $validated['is_active'],
            'created_by' => $actor?->getKey(),
            'updated_by' => $actor?->getKey(),
        ]);

        $this->activityLogger->record(
            $actor,
            ActivityLog::ACTION_CREATE,
            ActivityLog::MODULE_COLOR_MANAGEMENT,
            "Membuat warna {$color->code} - {$color->name}.",
            null,
            $this->auditableValues($color),
            $request,
        );

        return redirect()
            ->route('admin.colors.index')
            ->with('success', "Warna {$color->name} berhasil ditambahkan.");
    }

    public function edit(Color $color): View
    {
        return view('admin.colors.edit', compact('color'));
    }

    public function update(UpdateColorRequest $request, Color $color): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();
        $oldValues = $this->auditableValues($color);
        $oldStatus = (bool) $color->is_active;

        $color->fill([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'hex_code' => $validated['hex_code'] ?? null,
            'is_active' => $validated['is_active'],
            'updated_by' => $actor?->getKey(),
        ])->save();

        $this->activityLogger->record(
            $actor,
            ActivityLog::ACTION_UPDATE,
            ActivityLog::MODULE_COLOR_MANAGEMENT,
            "Memperbarui warna {$color->code} - {$color->name}.",
            $oldValues,
            $this->auditableValues($color),
            $request,
        );

        if ($oldStatus !== (bool) $color->is_active) {
            $this->activityLogger->record(
                $actor,
                $color->is_active ? ActivityLog::ACTION_ACTIVATE : ActivityLog::ACTION_DEACTIVATE,
                ActivityLog::MODULE_COLOR_MANAGEMENT,
                $color->is_active
                    ? "Mengaktifkan warna {$color->code} - {$color->name}."
                    : "Menonaktifkan warna {$color->code} - {$color->name}.",
                ['is_active' => $oldStatus],
                ['is_active' => (bool) $color->is_active],
                $request,
            );
        }

        return redirect()
            ->route('admin.colors.index')
            ->with('success', "Warna {$color->name} berhasil diperbarui.");
    }

    public function toggleStatus(Request $request, Color $color): RedirectResponse
    {
        $oldState = (bool) $color->is_active;
        $newState = ! $oldState;

        $color->forceFill([
            'is_active' => $newState,
            'updated_by' => $request->user()?->getKey(),
        ])->save();

        $this->activityLogger->record(
            $request->user(),
            $newState ? ActivityLog::ACTION_ACTIVATE : ActivityLog::ACTION_DEACTIVATE,
            ActivityLog::MODULE_COLOR_MANAGEMENT,
            $newState
                ? "Mengaktifkan warna {$color->code} - {$color->name}."
                : "Menonaktifkan warna {$color->code} - {$color->name}.",
            ['is_active' => $oldState],
            ['is_active' => $newState],
            $request,
        );

        return back()->with(
            'success',
            $newState
                ? "Warna {$color->name} berhasil diaktifkan."
                : "Warna {$color->name} berhasil dinonaktifkan."
        );
    }

    public function destroy(Request $request, Color $color): RedirectResponse
    {
        if ($this->isUsedByProductVariant($color)) {
            return back()->with(
                'error',
                "Warna {$color->name} sudah digunakan pada variasi produk dan tidak dapat dihapus. Nonaktifkan warna jika tidak ingin dipakai pada variasi baru."
            );
        }

        $oldValues = $this->auditableValues($color);
        $name = $color->name;
        $code = $color->code;

        $color->delete();

        $this->activityLogger->record(
            $request->user(),
            ActivityLog::ACTION_DELETE,
            ActivityLog::MODULE_COLOR_MANAGEMENT,
            "Menghapus warna {$code} - {$name} menggunakan soft delete.",
            $oldValues,
            ['deleted_at' => $color->deleted_at?->toDateTimeString()],
            $request,
        );

        return redirect()
            ->route('admin.colors.index')
            ->with('success', "Warna {$name} berhasil dihapus dari daftar aktif.");
    }

    private function isUsedByProductVariant(Color $color): bool
    {
        if (! Schema::hasTable('product_variants') || ! Schema::hasColumn('product_variants', 'color_id')) {
            return false;
        }

        $query = DB::table('product_variants')->where('color_id', $color->getKey());

        if (Schema::hasColumn('product_variants', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return $query->exists();
    }

    /** @return array<string, mixed> */
    private function auditableValues(Color $color): array
    {
        return [
            'id' => $color->id,
            'code' => $color->code,
            'name' => $color->name,
            'hex_code' => $color->hex_code,
            'is_active' => (bool) $color->is_active,
        ];
    }
}
