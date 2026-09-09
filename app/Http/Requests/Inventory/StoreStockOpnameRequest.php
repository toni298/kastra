<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\InertiaAuthorizationService;
use Illuminate\Validation\Rule;

class StoreStockOpnameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(InertiaAuthorizationService::class)->allows($this->user(), 'inventory.opname.create');
    }
    public function rules(): array
    {
        return [
            'source_type' => ['required', Rule::in(['gudang', 'cabang'])],
            'gudang_id' => ['nullable', 'uuid', Rule::requiredIf($this->input('source_type') === 'gudang'), Rule::exists('gudang', 'id')->where('company_id', $this->user()->company_id)->where('aktif', true)],
            'branch_id' => ['nullable', 'uuid', Rule::requiredIf($this->input('source_type') === 'cabang'), Rule::exists('branches', 'id')->where('company_id', $this->user()->company_id)->where('status', 'active')],
            'opname_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
