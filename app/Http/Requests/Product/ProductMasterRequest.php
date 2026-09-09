<?php

namespace App\Http\Requests\Product;

use App\Repositories\ProductMasterRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $master = (string) $this->route('master');
        abort_unless(in_array($master, ProductMasterRepository::MASTERS, true), 404);

        $action = match (true) {
            $this->isMethod('get') => 'view',
            $this->isMethod('post') => 'create',
            $this->isMethod('delete') => 'delete',
            default => 'edit',
        };

        return $this->user()->can("{$master}.{$action}");
    }

    public function rules(): array
    {
        $table = (string) $this->route('master');
        abort_unless(in_array($table, ProductMasterRepository::MASTERS, true), 404);

        if ($this->isMethod('get')) {
            $sorts = $table === 'units' ? ['name', 'code', 'is_active'] : ['name', 'is_active'];

            return [
                'search' => ['nullable', 'string', 'max:100'],
                'status' => ['nullable', 'in:active,inactive'],
                'cursor' => ['nullable', 'string'],
                'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
                'sort' => ['nullable', Rule::in($sorts)],
                'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
            ];
        }

        if ($this->isMethod('delete')) {
            return [];
        }

        $item = $this->route('item');

        $rules = [
            'name' => ['required', 'string', 'max:100', Rule::unique($table, 'name')->where('company_id', $this->user()->company_id)->ignore($item)],
            'is_active' => ['required', 'boolean'],
        ];

        if ($table === 'units') {
            $rules['code'] = [
                'required',
                'string',
                'max:30',
                Rule::unique('units', 'code')->where('company_id', $this->user()->company_id)->ignore($item),
            ];
        }

        return $rules;
    }
}
