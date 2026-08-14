<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateShipmentRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isInternalUser() ?? false; }
    public function rules(): array { return ['courier'=>['nullable','string','max:100'],'tracking_number'=>['nullable','string','max:100'],'status'=>['required',Rule::in(['pending','packed','in_transit','delivered'])],'description'=>['nullable','string','max:255']]; }
}
