<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Services\InertiaAuthorizationService;

class IndexStockOpnameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(InertiaAuthorizationService::class)->allows($this->user(), 'inventory.opname.view');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['draft', 'in_progress', 'completed', 'cancelled'])],
            'gudang_id' => ['nullable', 'uuid', Rule::exists('gudang', 'id')->where('company_id', $this->user()->company_id)],
            'branch_id' => ['nullable', 'uuid', Rule::exists('branches', 'id')->where('company_id', $this->user()->company_id)],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
        ];
    }
}
