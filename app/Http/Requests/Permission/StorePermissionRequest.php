<?php
namespace App\Http\Requests\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()->can('create', \App\Models\Permission::class); }
    public function rules(): array { return ['name' => ['required', 'string', 'max:100', 'regex:/^[a-z_]+\.[a-z_]+$/', Rule::unique('permissions', 'name')]]; }
}


