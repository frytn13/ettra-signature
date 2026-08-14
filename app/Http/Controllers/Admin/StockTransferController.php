<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreStockTransferRequest;
use App\Models\ActivityLog;
use App\Models\StockTransfer;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\ActivityLogger;
use App\Services\StockTransferService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockTransferController extends Controller
{
    public function __construct(private readonly StockTransferService $service, private readonly ActivityLogger $logger) {}

    public function index(Request $request): View
    {
        $search=trim((string)$request->query('search',''));
        $transfers=StockTransfer::query()->with(['sourceWarehouse:id,code,name','destinationWarehouse:id,code,name','processedBy:id,name'])->withCount('items')
            ->when($search,fn(Builder $q,$s)=>$q->where(fn(Builder $q)=>$q->where('transfer_number','like',"%{$s}%")->orWhereHas('sourceWarehouse',fn(Builder $w)=>$w->where('name','like',"%{$s}%"))->orWhereHas('destinationWarehouse',fn(Builder $w)=>$w->where('name','like',"%{$s}%"))))
            ->latest('transfer_date')->paginate(15)->withQueryString();
        return view('admin.stock-transfers.index',compact('transfers','search'));
    }

    public function create(): View
    {
        $warehouses=Warehouse::query()->where('is_active',true)->orderBy('name')->get(['id','code','name']);
        $stocks=WarehouseStock::query()->with(['warehouse:id,code,name','productVariant.product:id,name','productVariant.color:id,name','productVariant.size:id,name'])->whereRaw(WarehouseStock::availableSql().' > 0')->get();
        return view('admin.stock-transfers.create',compact('warehouses','stocks'));
    }

    public function store(StoreStockTransferRequest $request): RedirectResponse
    {
        $transfer=$this->service->process($request->integer('source_warehouse_id'),$request->integer('destination_warehouse_id'),$request->validated('items'),Carbon::parse($request->input('transfer_date')),$request->input('notes'),$request->user());
        $this->logger->record($request->user(),ActivityLog::ACTION_CREATE,ActivityLog::MODULE_STOCK_TRANSFER,"Memproses transfer {$transfer->transfer_number} dari {$transfer->sourceWarehouse?->name} ke {$transfer->destinationWarehouse?->name}.",null,['transfer_number'=>$transfer->transfer_number,'items'=>$transfer->items->count()],$request);
        return redirect()->route('admin.stock-transfers.index')->with('success','Transfer room berhasil diproses.');
    }

    public function show(StockTransfer $stockTransfer): View
    {
        $stockTransfer->load(['sourceWarehouse','destinationWarehouse','processedBy','items.productVariant.product','items.productVariant.color','items.productVariant.size']);
        return view('admin.stock-transfers.show',compact('stockTransfer'));
    }
}
