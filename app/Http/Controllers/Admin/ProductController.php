<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const MAX_PRODUCT_IMAGES = 8;

    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    /**
     * Menampilkan daftar produk untuk Owner dan Admin.
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'category' => (string) $request->query('category', ''),
            'status' => (string) $request->query('status', ''),
            'availability' => (string) $request->query('availability', ''),
            'visibility' => (string) $request->query('visibility', ''),
        ];

        $products = Product::query()
            ->with([
                'category:id,code,name',
                'primaryImage:id,product_id,path,is_primary',
                'updatedBy:id,name',
            ])
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('category', function (Builder $categoryQuery) use ($search): void {
                            $categoryQuery
                                ->where('code', 'like', "%{$search}%")
                                ->orWhere('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['category'] !== '', fn (Builder $query): Builder => $query->where('category_id', (int) $filters['category']))
            ->when($filters['status'] !== '', fn (Builder $query): Builder => $query->where('status', $filters['status']))
            ->when($filters['availability'] !== '', fn (Builder $query): Builder => $query->where('availability_status', $filters['availability']))
            ->when($filters['visibility'] === 'visible', fn (Builder $query): Builder => $query->where('is_visible', true))
            ->when($filters['visibility'] === 'hidden', fn (Builder $query): Builder => $query->where('is_visible', false))
            ->latest('updated_at')
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $statistics = [
            'total' => Product::query()->count(),
            'active' => Product::query()->where('status', Product::STATUS_ACTIVE)->count(),
            'visible' => Product::query()->where('status', Product::STATUS_ACTIVE)->where('is_visible', true)->count(),
            'archived' => Product::onlyTrashed()->count(),
        ];

        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'is_active']);

        return view('admin.products.index', [
            'products' => $products,
            'filters' => $filters,
            'statistics' => $statistics,
            'categories' => $categories,
            'statusOptions' => Product::statusOptions(),
            'availabilityOptions' => Product::availabilityOptions(),
            'isOwner' => $request->user()?->isOwner() === true,
        ]);
    }

    /**
     * Menampilkan formulir produk baru.
     */
    public function create(Request $request): View
    {
        return view('admin.products.create', [
            'product' => new Product([
                'status' => Product::STATUS_ACTIVE,
                'availability_status' => Product::AVAILABILITY_AVAILABLE,
                'is_visible' => true,
                'entry_date' => now()->toDateString(),
            ]),
            'categories' => Category::query()->active()->orderBy('name')->get(['id', 'code', 'name']),
            'statusOptions' => Product::statusOptions(),
            'availabilityOptions' => Product::availabilityOptions(),
            'isOwner' => $request->user()?->isOwner() === true,
        ]);
    }

    /**
     * Menyimpan produk baru beserta foto dan audit trail.
     */
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();
        $storedPaths = [];

        try {
            $product = DB::transaction(function () use ($request, $validated, $actor, &$storedPaths): Product {
                $category = Category::query()
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->findOrFail($validated['category_id']);

                $isAutomaticCode = empty($validated['code']);
                [$code, $sequence] = $isAutomaticCode
                    ? $this->generateAutomaticCode($category)
                    : [$validated['code'], null];

                $product = Product::query()->create([
                    'category_id' => $category->id,
                    'category_sequence' => $sequence,
                    'code' => $code,
                    'name' => $validated['name'],
                    'slug' => $this->generateUniqueSlug($validated['name']),
                    'description' => $validated['description'] ?? null,
                    'initial_purchase_price' => $actor?->isOwner() ? ($validated['initial_purchase_price'] ?? null) : null,
                    'cost_price' => $actor?->isOwner() ? ($validated['cost_price'] ?? null) : null,
                    'selling_price' => $validated['selling_price'],
                    'status' => $validated['status'],
                    'availability_status' => $validated['availability_status'],
                    'is_visible' => $validated['status'] === Product::STATUS_ACTIVE
                        ? $validated['is_visible']
                        : false,
                    'weight_grams' => $validated['weight_grams'] ?? null,
                    'entry_date' => $validated['entry_date'] ?? null,
                    'created_by' => $actor?->getKey(),
                    'updated_by' => $actor?->getKey(),
                ]);

                $this->storeUploadedImages($request, $product, $actor?->getKey(), $storedPaths);
                $this->ensurePrimaryImage($product);

                $this->activityLogger->record(
                    $actor,
                    ActivityLog::ACTION_CREATE,
                    ActivityLog::MODULE_PRODUCT_MANAGEMENT,
                    "Membuat produk {$product->code} - {$product->name}.",
                    null,
                    $this->auditableValues($product),
                    $request,
                );

                return $product;
            });
        } catch (\Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', "Produk {$product->name} berhasil ditambahkan.");
    }

    /**
     * Menampilkan detail produk. Data harga sensitif hanya dirender untuk Owner.
     */
    public function show(Request $request, Product $product): View
    {
        $product->load([
            'category:id,code,name,is_active',
            'images',
            'createdBy:id,name,role,deleted_at',
            'updatedBy:id,name,role,deleted_at',
        ]);

        return view('admin.products.show', [
            'product' => $product,
            'isOwner' => $request->user()?->isOwner() === true,
        ]);
    }

    /**
     * Menampilkan formulir edit produk.
     */
    public function edit(Request $request, Product $product): View
    {
        $product->load(['category:id,code,name,is_active', 'images']);

        $categories = Category::query()
            ->where(function (Builder $query) use ($product): void {
                $query->where('is_active', true)
                    ->orWhereKey($product->category_id);
            })
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'is_active']);

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $categories,
            'statusOptions' => Product::statusOptions(),
            'availabilityOptions' => Product::availabilityOptions(),
            'isOwner' => $request->user()?->isOwner() === true,
        ]);
    }

    /**
     * Memperbarui produk. Field harga sensitif tidak pernah diproses dari Admin.
     */
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();
        $oldValues = $this->auditableValues($product);
        $oldCategoryId = $product->category_id;

        $this->validateImageCapacity($request, $product);

        DB::transaction(function () use ($request, $validated, $actor, $product, $oldCategoryId): void {
            $data = [
                'category_id' => $validated['category_id'],
                'code' => $validated['code'],
                'name' => $validated['name'],
                'slug' => $this->generateUniqueSlug($validated['name'], $product),
                'description' => $validated['description'] ?? null,
                'selling_price' => $validated['selling_price'],
                'status' => $validated['status'],
                'availability_status' => $validated['availability_status'],
                'is_visible' => $validated['status'] === Product::STATUS_ACTIVE
                    ? $validated['is_visible']
                    : false,
                'weight_grams' => $validated['weight_grams'] ?? null,
                'entry_date' => $validated['entry_date'] ?? null,
                'updated_by' => $actor?->getKey(),
            ];

            if ((int) $oldCategoryId !== (int) $validated['category_id']) {
                $data['category_sequence'] = null;
            }

            if ($actor?->isOwner()) {
                $data['initial_purchase_price'] = $validated['initial_purchase_price'] ?? null;
                $data['cost_price'] = $validated['cost_price'] ?? null;
            }

            $product->fill($data)->save();

            $this->removeSelectedImages($request, $product);
            $this->replacePrimaryImage($request, $product, $actor?->getKey());
            $this->appendAdditionalImages($request, $product, $actor?->getKey());
            $this->ensurePrimaryImage($product);
        });

        $product->refresh();

        $this->activityLogger->record(
            $actor,
            ActivityLog::ACTION_UPDATE,
            ActivityLog::MODULE_PRODUCT_MANAGEMENT,
            "Memperbarui produk {$product->code} - {$product->name}.",
            $oldValues,
            $this->auditableValues($product),
            $request,
        );

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', "Produk {$product->name} berhasil diperbarui.");
    }

    /**
     * Mengaktifkan atau menonaktifkan produk dari halaman daftar.
     */
    public function toggleStatus(Request $request, Product $product): RedirectResponse
    {
        $oldStatus = $product->status;
        $oldVisibility = (bool) $product->is_visible;
        $newStatus = $oldStatus === Product::STATUS_ACTIVE
            ? Product::STATUS_INACTIVE
            : Product::STATUS_ACTIVE;

        $product->forceFill([
            'status' => $newStatus,
            'is_visible' => $newStatus === Product::STATUS_ACTIVE ? $oldVisibility : false,
            'updated_by' => $request->user()?->getKey(),
        ])->save();

        $this->activityLogger->record(
            $request->user(),
            $newStatus === Product::STATUS_ACTIVE ? ActivityLog::ACTION_ACTIVATE : ActivityLog::ACTION_DEACTIVATE,
            ActivityLog::MODULE_PRODUCT_MANAGEMENT,
            $newStatus === Product::STATUS_ACTIVE
                ? "Mengaktifkan produk {$product->code} - {$product->name}."
                : "Menonaktifkan produk {$product->code} - {$product->name} dan menyembunyikannya dari katalog pelanggan.",
            ['status' => $oldStatus, 'is_visible' => $oldVisibility],
            ['status' => $newStatus, 'is_visible' => (bool) $product->is_visible],
            $request,
        );

        return back()->with(
            'success',
            $newStatus === Product::STATUS_ACTIVE
                ? "Produk {$product->name} berhasil diaktifkan."
                : "Produk {$product->name} berhasil dinonaktifkan."
        );
    }

    /**
     * Mengatur apakah produk tampil pada halaman pelanggan.
     */
    public function toggleVisibility(Request $request, Product $product): RedirectResponse
    {
        if ($product->status !== Product::STATUS_ACTIVE && ! $product->is_visible) {
            return back()->with('error', 'Produk harus berstatus Aktif sebelum dapat ditampilkan pada katalog pelanggan.');
        }

        $oldVisibility = (bool) $product->is_visible;
        $newVisibility = ! $oldVisibility;

        $product->forceFill([
            'is_visible' => $newVisibility,
            'updated_by' => $request->user()?->getKey(),
        ])->save();

        $this->activityLogger->record(
            $request->user(),
            $newVisibility ? ActivityLog::ACTION_SHOW : ActivityLog::ACTION_HIDE,
            ActivityLog::MODULE_PRODUCT_MANAGEMENT,
            $newVisibility
                ? "Menampilkan produk {$product->code} - {$product->name} pada katalog pelanggan."
                : "Menyembunyikan produk {$product->code} - {$product->name} dari katalog pelanggan.",
            ['is_visible' => $oldVisibility],
            ['is_visible' => $newVisibility],
            $request,
        );

        return back()->with(
            'success',
            $newVisibility
                ? "Produk {$product->name} sekarang ditampilkan pada katalog pelanggan."
                : "Produk {$product->name} disembunyikan dari katalog pelanggan."
        );
    }

    /**
     * Mengarsipkan produk menggunakan soft delete.
     */
    public function destroy(Request $request, Product $product): RedirectResponse
    {
        if ($product->variants()->withTrashed()->exists()) {
            return back()->with(
                'error',
                'Produk ini sudah memiliki variasi sehingga tidak dapat diarsipkan. Nonaktifkan produk agar relasi variasi dan histori berikutnya tetap konsisten.'
            );
        }

        $oldValues = $this->auditableValues($product);
        $productName = $product->name;
        $productCode = $product->code;

        $product->delete();

        $this->activityLogger->record(
            $request->user(),
            ActivityLog::ACTION_DELETE,
            ActivityLog::MODULE_PRODUCT_MANAGEMENT,
            "Mengarsipkan produk {$productCode} - {$productName} menggunakan soft delete.",
            $oldValues,
            ['deleted_at' => $product->deleted_at?->toDateTimeString()],
            $request,
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', "Produk {$productName} berhasil diarsipkan.");
    }

    /**
     * Menghasilkan kode otomatis berdasarkan kode kategori dan nomor urut per kategori.
     * Lock kategori memastikan dua transaksi bersamaan tidak mengambil nomor yang sama.
     *
     * @return array{0:string,1:int}
     */
    private function generateAutomaticCode(Category $category): array
    {
        $sequence = ((int) Product::withTrashed()
            ->where('category_id', $category->id)
            ->max('category_sequence')) + 1;

        do {
            $code = sprintf('%s-%04d', $category->code, $sequence);

            if (! Product::withTrashed()->where('code', $code)->exists()) {
                break;
            }

            $sequence++;
        } while (true);

        return [$code, $sequence];
    }

    private function generateUniqueSlug(string $name, ?Product $currentProduct = null): string
    {
        $baseSlug = Str::slug($name);
        $baseSlug = $baseSlug !== '' ? $baseSlug : 'produk';
        $slug = $baseSlug;
        $suffix = 2;

        while (
            Product::withTrashed()
                ->where('slug', $slug)
                ->when($currentProduct, fn (Builder $query): Builder => $query->where($currentProduct->getKeyName(), '!=', $currentProduct->getKey()))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    private function validateImageCapacity(UpdateProductRequest $request, Product $product): void
    {
        $removeIds = collect($request->validated('remove_image_ids', []))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $currentCount = $product->images()->count();
        $removeCount = $product->images()->whereIn('id', $removeIds)->count();
        $currentPrimary = $product->images()->where('is_primary', true)->first();
        $removesCurrentPrimary = $currentPrimary !== null && $removeIds->contains($currentPrimary->id);
        $afterRemovalCount = max(0, $currentCount - $removeCount);
        $newCount = $afterRemovalCount;

        if ($request->hasFile('primary_image')) {
            $replacesExistingPrimary = $currentPrimary !== null && ! $removesCurrentPrimary;
            $newCount += $replacesExistingPrimary ? 0 : 1;
        }

        $newCount += count($request->file('additional_images', []));

        if ($newCount > self::MAX_PRODUCT_IMAGES) {
            throw ValidationException::withMessages([
                'additional_images' => 'Total foto produk maksimal '.self::MAX_PRODUCT_IMAGES.' gambar. Hapus beberapa foto lama atau kurangi foto baru.',
            ]);
        }
    }

    private function storeUploadedImages(Request $request, Product $product, ?int $actorId, array &$storedPaths): void
    {
        if ($request->hasFile('primary_image')) {
            $image = $request->file('primary_image');
            $storedPaths[] = $this->createImageRecord($product, $image, true, 0, $actorId)->path;
        }

        foreach ($request->file('additional_images', []) as $index => $image) {
            $storedPaths[] = $this->createImageRecord($product, $image, false, $index + 1, $actorId)->path;
        }
    }

    private function replacePrimaryImage(Request $request, Product $product, ?int $actorId): void
    {
        if (! $request->hasFile('primary_image')) {
            return;
        }

        $existingPrimary = $product->images()->where('is_primary', true)->first();

        if ($existingPrimary) {
            $this->deleteImage($existingPrimary);
        }

        $this->createImageRecord($product, $request->file('primary_image'), true, 0, $actorId);
    }

    private function appendAdditionalImages(Request $request, Product $product, ?int $actorId): void
    {
        $nextSortOrder = ((int) $product->images()->max('sort_order')) + 1;

        foreach ($request->file('additional_images', []) as $image) {
            $this->createImageRecord($product, $image, false, $nextSortOrder, $actorId);
            $nextSortOrder++;
        }
    }

    private function removeSelectedImages(Request $request, Product $product): void
    {
        $removeIds = collect($request->validated('remove_image_ids', []))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        if ($removeIds->isEmpty()) {
            return;
        }

        $product->images()
            ->whereIn('id', $removeIds)
            ->get()
            ->each(fn (ProductImage $image) => $this->deleteImage($image));
    }

    private function createImageRecord(Product $product, UploadedFile $image, bool $isPrimary, int $sortOrder, ?int $actorId): ProductImage
    {
        $path = $image->store("products/{$product->id}", 'public');

        return $product->images()->create([
            'path' => $path,
            'original_name' => $image->getClientOriginalName(),
            'mime_type' => $image->getMimeType(),
            'size_bytes' => $image->getSize(),
            'is_primary' => $isPrimary,
            'sort_order' => $sortOrder,
            'uploaded_by' => $actorId,
        ]);
    }

    private function deleteImage(ProductImage $image): void
    {
        Storage::disk('public')->delete($image->path);
        $image->delete();
    }

    private function ensurePrimaryImage(Product $product): void
    {
        if ($product->images()->where('is_primary', true)->exists()) {
            return;
        }

        $firstImage = $product->images()->orderBy('sort_order')->orderBy('id')->first();

        if ($firstImage) {
            $firstImage->forceFill(['is_primary' => true, 'sort_order' => 0])->save();
        }
    }

    /** @return array<string, mixed> */
    private function auditableValues(Product $product): array
    {
        return [
            'id' => $product->id,
            'category_id' => $product->category_id,
            'code' => $product->code,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'initial_purchase_price' => $product->initial_purchase_price,
            'cost_price' => $product->cost_price,
            'selling_price' => $product->selling_price,
            'status' => $product->status,
            'availability_status' => $product->availability_status,
            'is_visible' => (bool) $product->is_visible,
            'weight_grams' => $product->weight_grams,
            'entry_date' => $product->entry_date?->toDateString(),
        ];
    }
}
