<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePromotionRequest;
use App\Http\Requests\Admin\UpdatePromotionRequest;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
class PromotionController extends Controller
{
    public function __construct(private readonly ActivityLogger $logger) {}
    public function index(Request $request): View
    {
        $search=trim((string)$request->query('search','')); $status=(string)$request->query('status','');
        $promotions=Promotion::query()->with(['product:id,code,name','category:id,code,name'])->when($search,fn(Builder $q,$s)=>$q->where('name','like',"%{$s}%"))->when($status==='active',fn(Builder $q)=>$q->where('is_active',true))->when($status==='inactive',fn(Builder $q)=>$q->where('is_active',false))->latest('starts_at')->paginate(15)->withQueryString();
        return view('admin.promotions.index',compact('promotions','search','status'));
    }
    public function create(): View { return view('admin.promotions.create',['promotion'=>new Promotion(['is_active'=>true,'target_type'=>'all','discount_type'=>'percentage']),'products'=>$this->products(),'categories'=>$this->categories()]); }
    public function store(StorePromotionRequest $request): RedirectResponse
    {
        $data=$request->validated(); if($data['discount_type']==='percentage' && (float)$data['discount_value']>100) return back()->withErrors(['discount_value'=>'Diskon persentase tidak boleh lebih dari 100%.'])->withInput();
        $promotion=Promotion::query()->create($this->payload($data,$request));
        $this->logger->record($request->user(),ActivityLog::ACTION_CREATE,ActivityLog::MODULE_PROMOTION,"Membuat promosi {$promotion->name}.",null,['id'=>$promotion->id,'name'=>$promotion->name],$request);
        return redirect()->route('admin.promotions.index')->with('success','Promosi berhasil ditambahkan.');
    }
    public function edit(Promotion $promotion): View { return view('admin.promotions.edit',compact('promotion')+['products'=>$this->products(),'categories'=>$this->categories()]); }
    public function update(UpdatePromotionRequest $request, Promotion $promotion): RedirectResponse
    {
        $data=$request->validated(); if($data['discount_type']==='percentage' && (float)$data['discount_value']>100) return back()->withErrors(['discount_value'=>'Diskon persentase tidak boleh lebih dari 100%.'])->withInput();
        $old=$promotion->only(['name','discount_type','discount_value','target_type','product_id','category_id','starts_at','ends_at','is_active']); $promotion->fill($this->payload($data,$request,false))->save();
        $this->logger->record($request->user(),ActivityLog::ACTION_UPDATE,ActivityLog::MODULE_PROMOTION,"Memperbarui promosi {$promotion->name}.",$old,$promotion->only(array_keys($old)),$request);
        return redirect()->route('admin.promotions.index')->with('success','Promosi berhasil diperbarui.');
    }
    public function toggle(Request $request, Promotion $promotion): RedirectResponse
    {
        $oldStatus = (bool) $promotion->is_active;
        $promotion->forceFill(['is_active' => ! $oldStatus, 'updated_by' => $request->user()->id])->save();
        $this->logger->record($request->user(), ActivityLog::ACTION_UPDATE, ActivityLog::MODULE_PROMOTION, "Mengubah status promosi {$promotion->name}.", ['is_active' => $oldStatus], ['is_active' => (bool) $promotion->is_active], $request);
        return back()->with('success', 'Status promosi diperbarui.');
    }
    public function destroy(Request $request, Promotion $promotion): RedirectResponse { $promotion->delete(); $this->logger->record($request->user(),ActivityLog::ACTION_DELETE,ActivityLog::MODULE_PROMOTION,"Mengarsipkan promosi {$promotion->name}.",['id'=>$promotion->id],null,$request); return back()->with('success','Promosi diarsipkan.'); }
    private function payload(array $data, Request $request, bool $create=true): array
    {
        $payload = [
            'name' => $data['name'],
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'target_type' => $data['target_type'],
            'product_id' => $data['target_type'] === 'product' ? $data['product_id'] : null,
            'category_id' => $data['target_type'] === 'category' ? $data['category_id'] : null,
            'starts_at' => $data['starts_at'],
            'ends_at' => $data['ends_at'],
            'is_active' => $data['is_active'],
            'updated_by' => $request->user()->id,
        ];

        if ($create) {
            $payload['created_by'] = $request->user()->id;
        }

        return $payload;
    }
    private function products(){ return Product::query()->where('status','active')->orderBy('name')->get(['id','code','name']); }
    private function categories(){ return Category::query()->where('is_active',true)->orderBy('name')->get(['id','code','name']); }
}
