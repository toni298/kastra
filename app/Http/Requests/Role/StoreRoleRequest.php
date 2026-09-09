<?php

namespace App\Http\Requests\Role;

use App\Models\Role;
use App\Services\RbacService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Role::class);
    }

    public function rules(): array
    {
        return $this->roleRules();
    }

    protected function roleRules(?string $ignoreId = null): array
    {
        return [
            'name' => [
                'required',
                'string',
                'lowercase',
                'max:80',
                'regex:/^[a-z0-9_-]+$/',
                Rule::unique('roles', 'name')
                    ->where('company_id', $this->user()->company_id)
                    ->where('guard_name', 'web')
                    ->ignore($ignoreId),
            ],
            'permissions' => ['present', 'array'],
            'permissions.*' => [
                'string',
                'distinct',
                Rule::in(app(RbacService::class)->permissionsForFeatures($this->user()->company?->features)),
            ],
        ];
    }
}
