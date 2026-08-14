<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    /**
     * Menampilkan daftar master kategori untuk Owner dan Admin.
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'status' => (string) $request->query('status', ''),
        ];

        $categories = Category::query()
            ->with(['createdBy:id,name', 'updatedBy:id,name'])
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] === 'active', fn (Builder $query): Builder => $query->active())
            ->when($filters['status'] === 'inactive', fn (Builder $query): Builder => $query->inactive())
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $statistics = [
            'total' => Category::query()->count(),
            'active' => Category::query()->active()->count(),
            'inactive' => Category::query()->inactive()->count(),
            'archived' => Category::onlyTrashed()->count(),
        ];

        return view('admin.categories.index', compact('categories', 'filters', 'statistics'));
    }

    /**
     * Menampilkan formulir kategori baru.
     */
    public function create(): View
    {
        return view('admin.categories.create', [
            'category' => new Category(['is_active' => true]),
        ]);
    }

    /**
     * Menyimpan kategori baru dan mencatat audit trail.
     */
    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();

        $category = Category::query()->create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['name']),
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'],
            'created_by' => $actor?->getKey(),
            'updated_by' => $actor?->getKey(),
        ]);

        $this->activityLogger->record(
            $actor,
            ActivityLog::ACTION_CREATE,
            ActivityLog::MODULE_CATEGORY_MANAGEMENT,
            "Membuat kategori {$category->code} - {$category->name}.",
            null,
            $this->auditableCategoryValues($category),
            $request,
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Kategori {$category->name} berhasil ditambahkan.");
    }

    /**
     * Menampilkan formulir perubahan kategori.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Memperbarui kategori dan mencatat perubahan ke audit trail.
     */
    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();
        $oldValues = $this->auditableCategoryValues($category);
        $oldStatus = (bool) $category->is_active;

        $category->fill([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'slug' => $this->generateUniqueSlug($validated['name'], $category),
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'],
            'updated_by' => $actor?->getKey(),
        ])->save();

        $newValues = $this->auditableCategoryValues($category);

        $this->activityLogger->record(
            $actor,
            ActivityLog::ACTION_UPDATE,
            ActivityLog::MODULE_CATEGORY_MANAGEMENT,
            "Memperbarui kategori {$category->code} - {$category->name}.",
            $oldValues,
            $newValues,
            $request,
        );

        if ($oldStatus !== (bool) $category->is_active) {
            $this->activityLogger->record(
                $actor,
                $category->is_active ? ActivityLog::ACTION_ACTIVATE : ActivityLog::ACTION_DEACTIVATE,
                ActivityLog::MODULE_CATEGORY_MANAGEMENT,
                $category->is_active
                    ? "Mengaktifkan kategori {$category->code} - {$category->name}."
                    : "Menonaktifkan kategori {$category->code} - {$category->name}.",
                ['is_active' => $oldStatus],
                ['is_active' => (bool) $category->is_active],
                $request,
            );
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Kategori {$category->name} berhasil diperbarui.");
    }

    /**
     * Mengaktifkan atau menonaktifkan kategori langsung dari daftar.
     */
    public function toggleStatus(Request $request, Category $category): RedirectResponse
    {
        $oldState = (bool) $category->is_active;
        $newState = ! $oldState;

        $category->forceFill([
            'is_active' => $newState,
            'updated_by' => $request->user()?->getKey(),
        ])->save();

        $this->activityLogger->record(
            $request->user(),
            $newState ? ActivityLog::ACTION_ACTIVATE : ActivityLog::ACTION_DEACTIVATE,
            ActivityLog::MODULE_CATEGORY_MANAGEMENT,
            $newState
                ? "Mengaktifkan kategori {$category->code} - {$category->name}."
                : "Menonaktifkan kategori {$category->code} - {$category->name}.",
            ['is_active' => $oldState],
            ['is_active' => $newState],
            $request,
        );

        return back()->with(
            'success',
            $newState
                ? "Kategori {$category->name} berhasil diaktifkan."
                : "Kategori {$category->name} berhasil dinonaktifkan."
        );
    }

    /**
     * Menghapus kategori menggunakan soft delete.
     */
    public function destroy(Request $request, Category $category): RedirectResponse
    {
        if ($this->categoryHasProducts($category)) {
            return back()->with(
                'error',
                "Kategori {$category->name} masih digunakan oleh produk dan tidak dapat dihapus. Nonaktifkan kategori jika tidak ingin dipakai pada data baru."
            );
        }

        $oldValues = $this->auditableCategoryValues($category);
        $categoryName = $category->name;
        $categoryCode = $category->code;

        $category->delete();

        $this->activityLogger->record(
            $request->user(),
            ActivityLog::ACTION_DELETE,
            ActivityLog::MODULE_CATEGORY_MANAGEMENT,
            "Menghapus kategori {$categoryCode} - {$categoryName} menggunakan soft delete.",
            $oldValues,
            ['deleted_at' => $category->deleted_at?->toDateTimeString()],
            $request,
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', "Kategori {$categoryName} berhasil dihapus dari daftar aktif.");
    }

    /**
     * Membuat slug unik tanpa mengubah slug kategori lain yang sudah ada.
     */
    private function generateUniqueSlug(string $name, ?Category $currentCategory = null): string
    {
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'kategori';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            Category::withTrashed()
                ->where('slug', $slug)
                ->when($currentCategory, fn (Builder $query): Builder => $query->where($currentCategory->getKeyName(), '!=', $currentCategory->getKey()))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * Menjaga kategori yang sudah digunakan produk agar tidak terhapus.
     * Pemeriksaan otomatis aktif setelah modul products tersedia.
     */
    private function categoryHasProducts(Category $category): bool
    {
        if (! Schema::hasTable('products') || ! Schema::hasColumn('products', 'category_id')) {
            return false;
        }

        $query = DB::table('products')->where('category_id', $category->getKey());

        if (Schema::hasColumn('products', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        return $query->exists();
    }

    /**
     * Nilai aman yang dapat disimpan pada audit trail.
     *
     * @return array<string, mixed>
     */
    private function auditableCategoryValues(Category $category): array
    {
        return [
            'id' => $category->id,
            'code' => $category->code,
            'name' => $category->name,
            'slug' => $category->slug,
            'description' => $category->description,
            'is_active' => (bool) $category->is_active,
        ];
    }
}
