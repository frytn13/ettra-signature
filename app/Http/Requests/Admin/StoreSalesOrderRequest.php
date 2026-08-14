<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
class StoreSalesOrderRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isInternalUser() ?? false; }
    public function rules(): array { return [
        'channel'=>['required',Rule::in(['online','offline'])], 'warehouse_id'=>['required','integer','exists:warehouses,id'],
        'customer_name'=>['required','string','max:150'],'customer_phone'=>['nullable','string','max:30'],'shipping_address'=>['nullable','string','max:1500'],
        'shipping_cost'=>['required','numeric','min:0'],'payment_method'=>['required',Rule::in(['cash','bank_transfer','qris'])],
        'payment_proof'=>['required_unless:payment_method,cash','nullable','file','mimes:jpg,jpeg,png,webp,pdf','max:5120'],'transaction_date'=>['required','date','before_or_equal:now'],'notes'=>['nullable','string','max:1500'],
        'items'=>['required','array','min:1','max:50'],'items.*.product_variant_id'=>['required','integer','distinct','exists:product_variants,id'],'items.*.quantity'=>['required','integer','min:1'],
    ]; }
    public function after(): array { return [function(Validator $v){ if($this->input('channel')==='online' && $this->input('payment_method')==='cash') $v->errors()->add('payment_method','Pembayaran tunai hanya tersedia untuk transaksi offline.'); if($this->input('channel')==='online' && blank($this->input('shipping_address'))) $v->errors()->add('shipping_address','Alamat pengiriman wajib untuk transaksi online.'); }]; }
}
