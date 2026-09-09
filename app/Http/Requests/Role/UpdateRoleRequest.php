<?php

namespace App\Http\Requests\Role;

class UpdateRoleRequest extends StoreRoleRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('role'));
    }

    public function rules(): array
    {
        return $this->roleRules($this->route('role')->id);
    }
}
