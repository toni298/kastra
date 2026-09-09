<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class IndexOrganizationRequest extends FormRequest
{
    protected string $module;

    public function authorize(): bool
    {
        return $this->user()->can("{$this->module}.view");
    }

    public function rules(): array
    {
        $sorts = $this->module === 'cabang'
            ? ['name', 'code', 'status', 'id']
            : ['nama', 'kode', 'aktif', 'id'];

        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['aktif', 'nonaktif'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', Rule::in($sorts)],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
