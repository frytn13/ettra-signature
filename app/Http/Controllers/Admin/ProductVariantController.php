<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateProductVariantsRequest;
use App\Http\Requests\Admin\StoreProductVariantRequest;
use App\Http\Requests\Admin\UpdateProductVariantRequest;
use App\Models\ActivityLog;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Size;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProductVariantController extends Controller
{
    public function __construct(
        private readonly ActivityLogger $activityLogger,
    ) {
    }

    public function index(Request $request): View
    {
        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'product' => (string) $request->query('product', ''),
            'color' => (string) $request->query('color', ''),
            'size' => (string) $request->query('size', ''),
            'status' => (string) $request->query('status', ''),
        ];

        $variants = ProductVariant::query()
            ->with([
                'product:id,code,name,selling_price,weight_grams',
                'product.primaryImage:id,product_id,path,is_primary',
                'color:id,code,name,hex_code',
                'size:id,code,name,sort_order',
                'updatedBy:id,name',
            ])
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['search'];

                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('sku', 'like', "%{$search}%")
                        ->orWhereHas('product', fn (Builder $productQuery): Builder => $productQuery
                            ->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%"))
                        ->orWhereHas('color', fn (Builder $colorQuery): Builder => $colorQuery
                            ->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%"))
                        ->orWhereHas('size', fn (Builder $sizeQuery): Builder => $sizeQuery
                            ->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['product'] !== '', fn (Builder $query): Builder => $query->where('product_id', (int) $filters['product']))
            ->when($filters['color'] !== '', fn (Builder $query): Builder => $query->where('color_id', (int) $filters['color']))
            ->when($filters['size'] !== '', fn (Builder $query): Builder => $query->where('size_id', (int) $filters['size']))
            ->when($filters['status'] === 'active', fn (Builder $query): Builder => $query->active())
            ->when($filters['status'] === 'inactive', fn (Builder $query): Builder => $query->inactive())
            ->latest('updated_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $statistics = [
            'total' => ProductVariant::query()->count(),
            'active' => ProductVariant::query()->active()->count(),
            'inactive' => ProductVariant::query()->inactive()->count(),
            'archived' => ProductVariant::onlyTrashed()->count(),
        ];

        return view('admin.product-variants.index', [
            'variants' => $variants,
            'filters' => $filters,
            'statistics' => $statistics,
            'products' => Product::query()->orderBy('name')->get(['id', 'code', 'name']),
            'colors' => Color::query()->orderBy('name')->get(['id', 'code', 'name', 'hex_code']),
            'sizes' => Size::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.product-variants.create', [
            'productVariant' => new ProductVariant([
                'product_id' => $request->integer('product') ?: null,
                'additional_price' => 0,
                'is_active' => true,
            ]),
            'products' => $this->productOptions(),
            'colors' => Color::query()->active()->orderBy('name')->get(['id', 'code', 'name', 'hex_code']),
            'sizes' => Size::query()->active()->orderBy('sort_order')->orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function store(StoreProductVariantRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();

        $variant = DB::transaction(function () use ($validated, $actor): ProductVariant {
            $product = Product::query()->lockForUpdate()->findOrFail($validated['product_id']);
            $color = Color::query()->findOrFail($validated['color_id']);
            $size = Size::query()->findOrFail($validated['size_id']);
            $sku = $validated['sku'] ?: $this->generateSku($product, $color, $size);

            if (ProductVariant::withTrashed()->where('sku', $sku)->exists()) {
                throw ValidationException::withMessages([
                    'sku' => 'SKU otomatis tersebut sudah digunakan. Isi SKU lain secara manual.',
                ]);
            }

            return ProductVariant::query()->create([
                'product_id' => $product->id,
                'color_id' => $color->id,
                'size_id' => $size->id,
                'sku' => $sku,
                'additional_price' => $validated['additional_price'],
                'weight_grams' => $validated['weight_grams'] ?? null,
                'is_active' => $validated['is_active'],
                'created_by' => $actor?->getKey(),
                'updated_by' => $actor?->getKey(),
            ]);
        });

        $variant->load(['product:id,code,name', 'color:id,code,name', 'size:id,code,name']);

        $this->activityLogger->record(
            $actor,
            ActivityLog::ACTION_CREATE,
            ActivityLog::MODULE_PRODUCT_VARIANT_MANAGEMENT,
            "Membuat variasi {$variant->sku} untuk produk {$variant->product?->code} - {$variant->product?->name}.",
            null,
            $this->auditableValues($variant),
            $request,
        );

        return redirect()
            ->route('admin.product-variants.index', ['product' => $variant->product_id])
            ->with('success', "Variasi {$variant->sku} berhasil ditambahkan.");
    }

    public function generateForm(Request $request): View
    {
        return view('admin.product-variants.generate', [
            'selectedProductId' => $request->integer('product') ?: null,
            'products' => $this->productOptions(),
            'colors' => Color::query()->active()->orderBy('name')->get(['id', 'code', 'name', 'hex_code']),
            'sizes' => Size::query()->active()->orderBy('sort_order')->orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function generate(GenerateProductVariantsRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();
        $created = [];
        $skipped = 0;

        DB::transaction(function () use ($validated, $actor, &$created, &$skipped): void {
            $product = Product::query()->lockForUpdate()->findOrFail($validated['product_id']);
            $colors = Color::query()->whereIn('id', $validated['color_ids'])->get()->keyBy('id');
            $sizes = Size::query()->whereIn('id', $validated['size_ids'])->get()->keyBy('id');

            foreach ($validated['color_ids'] as $colorId) {
                foreach ($validated['size_ids'] as $sizeId) {
                    $color = $colors->get((int) $colorId);
                    $size = $sizes->get((int) $sizeId);

                    if (! $color || ! $size) {
                        continue;
                    }

                    $alreadyExists = ProductVariant::withTrashed()
                        ->where('product_id', $product->id)
                        ->where('color_id', $color->id)
                        ->where('size_id', $size->id)
                        ->exists();

                    if ($alreadyExists) {
                        $skipped++;
                        continue;
                    }

                    $sku = $this->generateSku($product, $color, $size);

                    if (ProductVariant::withTrashed()->where('sku', $sku)->exists()) {
                        $skipped++;
                        continue;
                    }

                    $variant = ProductVariant::query()->create([
                        'product_id' => $product->id,
                        'color_id' => $color->id,
                        'size_id' => $size->id,
                        'sku' => $sku,
                        'additional_price' => $validated['additional_price'],
                        'weight_grams' => $validated['weight_grams'] ?? null,
                        'is_active' => $validated['is_active'],
                        'created_by' => $actor?->getKey(),
                        'updated_by' => $actor?->getKey(),
                    ]);

                    $created[] = [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'color' => $color->name,
                        'size' => $size->name,
                    ];
                }
            }
        });

        $product = Product::query()->findOrFail($validated['product_id']);

        $this->activityLogger->record(
            $actor,
            ActivityLog::ACTION_CREATE,
            ActivityLog::MODULE_PRODUCT_VARIANT_MANAGEMENT,
            'Generate kombinasi variasi untuk produk '.$product->code.' - '.$product->name.'.',
            null,
            [
                'product_id' => $product->id,
                'product_code' => $product->code,
                'created_count' => count($created),
                'skipped_count' => $skipped,
                'variants' => $created,
            ],
            $request,
        );

        $message = count($created).' variasi berhasil dibuat.';

        if ($skipped > 0) {
            $message .= " {$skipped} kombinasi dilewati karena sudah tersedia atau pernah diarsipkan.";
        }

        return redirect()
            ->route('admin.product-variants.index', ['product' => $product->id])
            ->with(count($created) > 0 ? 'success' : 'error', $message);
    }

    public function edit(ProductVariant $productVariant): View
    {
        $productVariant->load(['product:id,code,name,selling_price,weight_grams', 'color:id,code,name,is_active', 'size:id,code,name,is_active']);

        return view('admin.product-variants.edit', [
            'productVariant' => $productVariant,
            'products' => $this->productOptions($productVariant->product_id),
            'colors' => Color::query()
                ->where(fn (Builder $query): Builder => $query->where('is_active', true)->orWhereKey($productVariant->color_id))
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'hex_code', 'is_active']),
            'sizes' => Size::query()
                ->where(fn (Builder $query): Builder => $query->where('is_active', true)->orWhereKey($productVariant->size_id))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'code', 'name', 'is_active']),
        ]);
    }

    public function update(UpdateProductVariantRequest $request, ProductVariant $productVariant): RedirectResponse
    {
        $validated = $request->validated();
        $actor = $request->user();
        $productVariant->load(['product:id,code,name', 'color:id,code,name', 'size:id,code,name']);
        $oldValues = $this->auditableValues($productVariant);
        $oldStatus = (bool) $productVariant->is_active;

        $productVariant->fill([
            'product_id' => $validated['product_id'],
            'color_id' => $validated['color_id'],
            'size_id' => $validated['size_id'],
            'sku' => $validated['sku'],
            'additional_price' => $validated['additional_price'],
            'weight_grams' => $validated['weight_grams'] ?? null,
            'is_active' => $validated['is_active'],
            'updated_by' => $actor?->getKey(),
        ])->save();

        $productVariant->refresh()->load(['product:id,code,name', 'color:id,code,name', 'size:id,code,name']);

        $this->activityLogger->record(
            $actor,
            ActivityLog::ACTION_UPDATE,
            ActivityLog::MODULE_PRODUCT_VARIANT_MANAGEMENT,
            "Memperbarui variasi {$productVariant->sku}.",
            $oldValues,
            $this->auditableValues($productVariant),
            $request,
        );

        if ($oldStatus !== (bool) $productVariant->is_active) {
            $this->activityLogger->record(
                $actor,
                $productVariant->is_active ? ActivityLog::ACTION_ACTIVATE : ActivityLog::ACTION_DEACTIVATE,
                ActivityLog::MODULE_PRODUCT_VARIANT_MANAGEMENT,
                $productVariant->is_active
                    ? "Mengaktifkan variasi {$productVariant->sku}."
                    : "Menonaktifkan variasi {$productVariant->sku}.",
                ['is_active' => $oldStatus],
                ['is_active' => (bool) $productVariant->is_active],
                $request,
            );
        }

        return redirect()
            ->route('admin.product-variants.index', ['product' => $productVariant->product_id])
            ->with('success', "Variasi {$productVariant->sku} berhasil diperbarui.");
    }

    public function toggleStatus(Request $request, ProductVariant $productVariant): RedirectResponse
    {
        $oldStatus = (bool) $productVariant->is_active;
        $productVariant->forceFill([
            'is_active' => ! $oldStatus,
            'updated_by' => $request->user()?->getKey(),
        ])->save();

        $this->activityLogger->record(
            $request->user(),
            $productVariant->is_active ? ActivityLog::ACTION_ACTIVATE : ActivityLog::ACTION_DEACTIVATE,
            ActivityLog::MODULE_PRODUCT_VARIANT_MANAGEMENT,
            $productVariant->is_active
                ? "Mengaktifkan variasi {$productVariant->sku}."
                : "Menonaktifkan variasi {$productVariant->sku}.",
            ['is_active' => $oldStatus],
            ['is_active' => (bool) $productVariant->is_active],
            $request,
        );

        return back()->with(
            'success',
            $productVariant->is_active
                ? "Variasi {$productVariant->sku} berhasil diaktifkan."
                : "Variasi {$productVariant->sku} berhasil dinonaktifkan."
        );
    }

    public function destroy(Request $request, ProductVariant $productVariant): RedirectResponse
    {
        if ($this->hasTransactionalDependencies($productVariant)) {
            return back()->with(
                'error',
                'Variasi ini sudah memiliki riwayat stok atau transaksi sehingga tidak dapat dihapus. Nonaktifkan variasi agar histori tetap konsisten.'
            );
        }

        $productVariant->load(['product:id,code,name', 'color:id,code,name', 'size:id,code,name']);
        $oldValues = $this->auditableValues($productVariant);
        $sku = $productVariant->sku;
        $productId = $productVariant->product_id;
        $productVariant->delete();

        $this->activityLogger->record(
            $request->user(),
            ActivityLog::ACTION_DELETE,
            ActivityLog::MODULE_PRODUCT_VARIANT_MANAGEMENT,
            "Mengarsipkan variasi {$sku} menggunakan soft delete.",
            $oldValues,
            ['deleted_at' => $productVariant->deleted_at?->toDateTimeString()],
            $request,
        );

        return redirect()
            ->route('admin.product-variants.index', ['product' => $productId])
            ->with('success', "Variasi {$sku} berhasil diarsipkan.");
    }

    private function productOptions(?int $includeProductId = null)
    {
        return Product::query()
            ->where(function (Builder $query) use ($includeProductId): void {
                $query->where('status', '!=', Product::STATUS_DISCONTINUED);

                if ($includeProductId) {
                    $query->orWhereKey($includeProductId);
                }
            })
            ->orderBy('name')
            ->get(['id', 'code', 'name', 'selling_price', 'weight_grams', 'status']);
    }

    private function generateSku(Product $product, Color $color, Size $size): string
    {
        $raw = Str::upper($product->code.'-'.$color->code.'-'.$size->code);
        $sku = preg_replace('/[^A-Z0-9_-]+/', '-', $raw) ?? $raw;
        $sku = preg_replace('/-+/', '-', $sku) ?? $sku;

        return trim($sku, '-_');
    }

    private function hasTransactionalDependencies(ProductVariant $productVariant): bool
    {
        $dependencies = [
            ['warehouse_stocks', 'product_variant_id'],
            ['stock_movements', 'product_variant_id'],
            ['order_items', 'product_variant_id'],
            ['purchase_order_items', 'product_variant_id'],
            ['goods_receipt_items', 'product_variant_id'],
        ];

        foreach ($dependencies as [$table, $column]) {
            if (
                Schema::hasTable($table)
                && Schema::hasColumn($table, $column)
                && DB::table($table)->where($column, $productVariant->id)->exists()
            ) {
                return true;
            }
        }

        return false;
    }

    /** @return array<string, mixed> */
    private function auditableValues(ProductVariant $productVariant): array
    {
        $productVariant->loadMissing(['product:id,code,name', 'color:id,code,name', 'size:id,code,name']);

        return [
            'id' => $productVariant->id,
            'product_id' => $productVariant->product_id,
            'product' => $productVariant->product?->code.' - '.$productVariant->product?->name,
            'color_id' => $productVariant->color_id,
            'color' => $productVariant->color?->code.' - '.$productVariant->color?->name,
            'size_id' => $productVariant->size_id,
            'size' => $productVariant->size?->code.' - '.$productVariant->size?->name,
            'sku' => $productVariant->sku,
            'additional_price' => (float) $productVariant->additional_price,
            'weight_grams' => $productVariant->weight_grams,
            'is_active' => (bool) $productVariant->is_active,
        ];
    }
}
