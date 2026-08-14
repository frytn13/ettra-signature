<?php
namespace App\Http\Requests\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdatePaymentRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->isInternalUser() ?? false; }
    public function rules(): array { return ['decision'=>['required',Rule::in(['verified','rejected'])],'rejection_reason'=>['nullable','required_if:decision,rejected','string','max:1000'],'notes'=>['nullable','string','max:1000']]; }
}
