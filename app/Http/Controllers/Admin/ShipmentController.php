<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateShipmentRequest;
use App\Models\ActivityLog;
use App\Models\Shipment;
use App\Models\ShipmentHistory;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
class ShipmentController extends Controller
{
    public function __construct(private readonly ActivityLogger $logger) {}
    public function index(Request $request): View
    {
        $status=(string)$request->query('status',''); $search=trim((string)$request->query('search',''));
        $shipments=Shipment::query()->with(['order:id,transaction_number,customer_name,shipping_address,order_status,payment_status','updatedBy:id,name'])
            ->when($status,fn(Builder $q,$v)=>$q->where('status',$v))->when($search,fn(Builder $q,$s)=>$q->where(fn(Builder $q)=>$q->where('tracking_number','like',"%{$s}%")->orWhere('courier','like',"%{$s}%")->orWhereHas('order',fn(Builder $o)=>$o->where('transaction_number','like',"%{$s}%")->orWhere('customer_name','like',"%{$s}%"))))->latest()->paginate(15)->withQueryString();
        return view('admin.shipments.index',compact('shipments','status','search'));
    }
    public function edit(Shipment $shipment): View { $shipment->load('order'); return view('admin.shipments.edit',compact('shipment')); }
    public function update(UpdateShipmentRequest $request, Shipment $shipment): RedirectResponse
    {
        DB::transaction(function() use($request,$shipment){
            $shipment=Shipment::query()->with('order')->lockForUpdate()->findOrFail($shipment->id); $old=$shipment->status; $new=$request->input('status');
            $payload=['courier'=>$request->input('courier'),'tracking_number'=>$request->input('tracking_number'),'status'=>$new,'updated_by'=>$request->user()->id];
            if($new==='packed' && !$shipment->packed_at) $payload['packed_at']=now(); if($new==='in_transit' && !$shipment->shipped_at) $payload['shipped_at']=now(); if($new==='delivered' && !$shipment->delivered_at) $payload['delivered_at']=now();
            $shipment->forceFill($payload)->save();
            if($old!==$new) ShipmentHistory::query()->create(['shipment_id'=>$shipment->id,'status'=>$new,'description'=>$request->input('description') ?: $shipment->statusLabel(),'updated_by'=>$request->user()->id,'created_at'=>now()]);
            $orderStatus=match($new){'packed'=>'packed','in_transit'=>'shipped','delivered'=>'completed',default=>$shipment->order->order_status};
            $shipment->order->forceFill(['order_status'=>$orderStatus,'updated_by'=>$request->user()->id])->save();
        });
        $this->logger->record($request->user(),ActivityLog::ACTION_UPDATE,ActivityLog::MODULE_SHIPMENT,"Memperbarui status pengiriman transaksi {$shipment->order?->transaction_number}.",null,['status'=>$request->input('status'),'tracking_number'=>$request->input('tracking_number')],$request);
        return redirect()->route('admin.shipments.index')->with('success','Pengiriman berhasil diperbarui.');
    }
}
