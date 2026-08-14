<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isInternalUser() ?? false; }
    public function rules(): array { return [
        'name'=>['required','string','max:150'],'discount_type'=>['required',Rule::in(['percentage','fixed'])],
        'discount_value'=>['required','numeric','min:0.01'],'target_type'=>['required',Rule::in(['all','product','category'])],
        'product_id'=>['nullable','required_if:target_type,product','exists:products,id'],'category_id'=>['nullable','required_if:target_type,category','exists:categories,id'],
        'starts_at'=>['required','date'],'ends_at'=>['required','date','after:starts_at'],'is_active'=>['required','boolean'],
    ]; }
}
