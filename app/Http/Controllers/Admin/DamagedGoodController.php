<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDamagedGoodRequest;
use App\Models\ActivityLog;
use App\Models\DamagedGood;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\ActivityLogger;
use App\Services\DamagedGoodService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DamagedGoodController extends Controller
{
    public function __construct(private readonly DamagedGoodService $service, private readonly ActivityLogger $logger) {}

    public function index(Request $request): View
    {
        $filters = ['search' => trim((string)$request->query('search','')), 'warehouse' => $request->query('warehouse'), 'action' => $request->query('action')];
        $records = DamagedGood::query()->with(['warehouse:id,code,name','productVariant.product:id,name','productVariant.color:id,name','productVariant.size:id,name','processedBy:id,name'])
            ->when($filters['search'], function (Builder $q, string $search): void { $q->where(fn(Builder $q) => $q->where('transaction_number','like',"%{$search}%")->orWhereHas('productVariant',fn(Builder $v)=>$v->where('sku','like',"%{$search}%"))); })
            ->when($filters['warehouse'], fn(Builder $q,$id)=>$q->where('warehouse_id',$id))
            ->when($filters['action'], fn(Builder $q,$a)=>$q->where('action',$a))
            ->latest('transaction_date')->paginate(15)->withQueryString();
        $warehouses = Warehouse::query()->where('is_active',true)->orderBy('name')->get(['id','code','name']);
        return view('admin.damaged-goods.index', compact('records','warehouses','filters'));
    }

    public function create(Request $request): View
    {
        $stocks = WarehouseStock::query()->with(['warehouse:id,code,name','productVariant.product:id,name','productVariant.color:id,name','productVariant.size:id,name'])->whereHas('warehouse',fn($q)=>$q->where('is_active',true))->orderBy('warehouse_id')->get();
        return view('admin.damaged-goods.create', ['stocks'=>$stocks,'selectedStockId'=>$request->integer('stock') ?: null]);
    }

    public function store(StoreDamagedGoodRequest $request): RedirectResponse
    {
        $stock = WarehouseStock::query()->findOrFail($request->integer('warehouse_stock_id'));
        $record = $this->service->process($stock, $request->string('action')->toString(), $request->integer('quantity'), $request->string('reason')->toString(), $request->input('notes'), Carbon::parse($request->input('transaction_date')), $request->user());
        $this->logger->record($request->user(), ActivityLog::ACTION_CREATE, ActivityLog::MODULE_DAMAGED_GOODS, "Mencatat {$record->actionLabel()} {$record->transaction_number} sebanyak {$record->quantity} unit.", null, ['transaction_number'=>$record->transaction_number,'quantity'=>$record->quantity,'action'=>$record->action], $request);
        return redirect()->route('admin.damaged-goods.index')->with('success','Transaksi barang rusak berhasil dicatat.');
    }

    public function show(DamagedGood $damagedGood): View
    {
        $damagedGood->load(['warehouse','warehouseStock','productVariant.product','productVariant.color','productVariant.size','processedBy','stockMovement']);
        return view('admin.damaged-goods.show', compact('damagedGood'));
    }
}
