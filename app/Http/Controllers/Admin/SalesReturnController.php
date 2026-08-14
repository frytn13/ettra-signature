<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSalesReturnRequest;
use App\Models\ActivityLog;
use App\Models\SalesOrder;
use App\Models\SalesReturn;
use App\Services\ActivityLogger;
use App\Services\SalesReturnService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesReturnController extends Controller
{
    public function __construct(private readonly SalesReturnService $service, private readonly ActivityLogger $logger) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $status = (string) $request->query('refund_status', '');
        $returns = SalesReturn::query()
            ->with(['order:id,transaction_number,customer_name', 'warehouse:id,code,name', 'processedBy:id,name'])
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $q) => $q
                ->where('return_number', 'like', "%{$search}%")
                ->orWhereHas('order', fn (Builder $order) => $order->where('transaction_number', 'like', "%{$search}%")->orWhere('customer_name', 'like', "%{$search}%"))))
            ->when($status !== '', fn (Builder $query) => $query->where('refund_status', $status))
            ->latest('return_date')->paginate(15)->withQueryString();
        return view('admin.sales-returns.index', compact('returns', 'search', 'status'));
    }

    public function create(Request $request): View
    {
        $orders = SalesOrder::query()
            ->where('payment_status', 'paid')
            ->with(['items:id,sales_order_id,product_variant_id,sku_snapshot,product_name_snapshot,variant_snapshot,quantity,subtotal'])
            ->latest('transaction_date')
            ->limit(100)
            ->get();
        return view('admin.sales-returns.create', ['orders' => $orders, 'selectedOrderId' => $request->integer('order') ?: null]);
    }

    public function store(StoreSalesReturnRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $order = SalesOrder::query()->findOrFail((int) $validated['sales_order_id']);
        $return = $this->service->create(
            $order,
            $validated['items'],
            $validated['reason'],
            $validated['notes'] ?? null,
            Carbon::parse($validated['return_date']),
            $request->boolean('refund_requested'),
            $request->user(),
        );
        $this->logger->record($request->user(), ActivityLog::ACTION_CREATE, ActivityLog::MODULE_SALES, "Mencatat retur pelanggan {$return->return_number} untuk {$order->transaction_number}.", null, ['return_number' => $return->return_number, 'sales_order_id' => $order->id, 'refund_amount' => $return->refund_amount], $request);
        return redirect()->route('admin.sales-returns.index')->with('success', 'Retur pelanggan berhasil diproses dan stok telah diperbarui.');
    }

    public function show(SalesReturn $salesReturn): View
    {
        $salesReturn->load(['order', 'warehouse', 'processedBy', 'items.orderItem', 'items.productVariant.product', 'items.productVariant.color', 'items.productVariant.size']);
        return view('admin.sales-returns.show', compact('salesReturn'));
    }
}
