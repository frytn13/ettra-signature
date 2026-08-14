<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSalesOrderRequest;
use App\Models\ActivityLog;
use App\Models\ProductVariant;
use App\Models\SalesOrder;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use App\Services\ActivityLogger;
use App\Services\SalesOrderService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class SalesOrderController extends Controller
{
    public function __construct(private readonly SalesOrderService $service, private readonly ActivityLogger $logger) {}

    public function index(Request $request): View
    {
        $filters = [
            'search'=>trim((string)$request->query('search','')),
            'channel'=>(string)$request->query('channel',''),
            'payment'=>(string)$request->query('payment',''),
            'status'=>(string)$request->query('status',''),
        ];
        $orders = SalesOrder::query()->with(['warehouse:id,code,name','createdBy:id,name'])->withCount('items')
            ->when($filters['search'],fn(Builder $q,$s)=>$q->where(fn(Builder $q)=>$q->where('transaction_number','like',"%{$s}%")->orWhere('customer_name','like',"%{$s}%")->orWhere('customer_phone','like',"%{$s}%")))
            ->when($filters['channel'],fn(Builder $q,$v)=>$q->where('channel',$v))
            ->when($filters['payment'],fn(Builder $q,$v)=>$q->where('payment_status',$v))
            ->when($filters['status'],fn(Builder $q,$v)=>$q->where('order_status',$v))
            ->latest('transaction_date')->paginate(15)->withQueryString();
        $stats = [
            'today'=>SalesOrder::query()->whereDate('transaction_date',today())->count(),
            'online'=>SalesOrder::query()->online()->whereDate('transaction_date',today())->count(),
            'offline'=>SalesOrder::query()->offline()->whereDate('transaction_date',today())->count(),
            'revenue'=>SalesOrder::query()->where('payment_status','paid')->whereDate('transaction_date',today())->sum('grand_total'),
        ];
        return view('admin.sales.index',compact('orders','filters','stats'));
    }

    public function create(): View
    {
        $warehouses=Warehouse::query()->where('is_active',true)->orderBy('name')->get(['id','code','name']);
        $stocks=WarehouseStock::query()->with(['warehouse:id,code,name','productVariant.product:id,name,selling_price,status','productVariant.color:id,name','productVariant.size:id,name'])
            ->whereRaw(WarehouseStock::availableSql().' > 0')->get();
        return view('admin.sales.create',compact('warehouses','stocks'));
    }

    public function store(StoreSalesOrderRequest $request): RedirectResponse
    {
        $proofPath = null;
        if ($request->hasFile('payment_proof')) $proofPath = $request->file('payment_proof')->store('payment-proofs','local');
        try {
            $data=$request->validated(); $data['transaction_date']=Carbon::parse($data['transaction_date']);
            $order=$this->service->create($data,$data['items'],$proofPath,$request->user());
        } catch (Throwable $e) {
            if($proofPath) Storage::disk('local')->delete($proofPath);
            throw $e;
        }
        $this->logger->record($request->user(),ActivityLog::ACTION_CREATE,ActivityLog::MODULE_SALES,"Membuat transaksi {$order->transaction_number} ({$order->channelLabel()}).",null,['transaction_number'=>$order->transaction_number,'grand_total'=>$order->grand_total],$request);
        return redirect()->route('admin.sales.index')->with('success','Transaksi penjualan berhasil dibuat.');
    }

    public function show(SalesOrder $sale): View
    {
        $sale->load(['warehouse','items.productVariant.product','payments.verifiedBy','shipment.histories.updatedBy','createdBy','verifiedBy']);
        return view('admin.sales.show',['order'=>$sale,'isOwner'=>request()->user()?->isOwner()]);
    }

    public function cancel(Request $request, SalesOrder $sale): RedirectResponse
    {
        $this->service->cancel($sale,$request->user());
        $this->logger->record($request->user(),ActivityLog::ACTION_UPDATE,ActivityLog::MODULE_SALES,"Membatalkan transaksi {$sale->transaction_number}.",null,['order_status'=>'cancelled'],$request);
        return back()->with('success','Pesanan berhasil dibatalkan.');
    }

    public function document(SalesOrder $sale, string $type): View
    {
        abort_unless(in_array($type,['invoice','nota','kwitansi'],true),404);
        $sale->load(['warehouse','items','payments.verifiedBy','createdBy']);
        return view('admin.sales.document',['order'=>$sale,'documentType'=>$type]);
    }
}
