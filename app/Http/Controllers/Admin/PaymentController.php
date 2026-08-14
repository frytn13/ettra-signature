<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePaymentRequest;
use App\Models\ActivityLog;
use App\Models\Payment;
use App\Services\ActivityLogger;
use App\Services\SalesOrderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
class PaymentController extends Controller
{
    public function __construct(private readonly SalesOrderService $service, private readonly ActivityLogger $logger) {}
    public function index(Request $request): View
    {
        $status=(string)$request->query('status',''); $search=trim((string)$request->query('search',''));
        $payments=Payment::query()->with(['order:id,transaction_number,customer_name,payment_status,grand_total','verifiedBy:id,name'])
            ->when($status,fn(Builder $q,$v)=>$q->where('status',$v))->when($search,fn(Builder $q,$s)=>$q->where(fn(Builder $q)=>$q->where('payment_number','like',"%{$s}%")->orWhereHas('order',fn(Builder $o)=>$o->where('transaction_number','like',"%{$s}%")->orWhere('customer_name','like',"%{$s}%"))))->latest()->paginate(15)->withQueryString();
        return view('admin.payments.index',compact('payments','status','search'));
    }
    public function edit(Payment $payment): View { $payment->load('order'); return view('admin.payments.edit',compact('payment')); }
    public function update(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        if($request->input('decision')==='verified') $payment=$this->service->verifyPayment($payment,$request->user(),$request->input('notes'));
        else $payment=$this->service->rejectPayment($payment,$request->user(),$request->input('rejection_reason'),$request->input('notes'));
        $this->logger->record($request->user(),ActivityLog::ACTION_UPDATE,ActivityLog::MODULE_PAYMENT,"Memproses pembayaran {$payment->payment_number}: {$payment->statusLabel()}.",null,['status'=>$payment->status],$request);
        return redirect()->route('admin.payments.index')->with('success','Status pembayaran berhasil diperbarui.');
    }
    public function proof(Payment $payment)
    {
        abort_unless($payment->proof_path && Storage::disk('local')->exists($payment->proof_path),404);
        return Storage::disk('local')->response($payment->proof_path);
    }
}
