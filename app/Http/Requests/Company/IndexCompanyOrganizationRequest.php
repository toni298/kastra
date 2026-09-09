<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexCompanyOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $module = $this->routeIs('company.branches') ? 'cabang' : 'gudang';

        return $this->user()->can("{$module}.view");
    }

    public function rules(): array
    {
        $sorts = $this->routeIs('company.branches')
            ? ['name', 'code', 'status', 'id']
            : ['nama', 'kode', 'aktif', 'id'];

        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:aktif,nonaktif'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', Rule::in($sorts)],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
