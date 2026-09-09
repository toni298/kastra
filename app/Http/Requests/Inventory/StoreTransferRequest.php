<?php

namespace App\Http\Requests\Inventory;

use App\Services\InertiaAuthorizationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(InertiaAuthorizationService::class)->allows($this->user(), 'inventory.transfers.create');
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'source_gudang_id' => ['required', 'uuid', Rule::exists('gudang', 'id')->where('company_id', $companyId)->where('aktif', true)],
            'destination_type' => ['required', Rule::in(['gudang', 'branch'])],
            'destination_gudang_id' => ['nullable', 'uuid', 'different:source_gudang_id', Rule::requiredIf($this->input('destination_type') === 'gudang'), Rule::exists('gudang', 'id')->where('company_id', $companyId)->where('aktif', true)],
            'destination_branch_id' => ['nullable', 'uuid', Rule::requiredIf($this->input('destination_type') === 'branch'), Rule::exists('branches', 'id')->where('company_id', $companyId)->where('status', 'active')],
            'transfer_date' => ['required', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.source_stock_id' => [
                'required',
                'uuid',
                'distinct',
                Rule::exists('product_stocks', 'id')
                    ->where('company_id', $companyId)
                    ->where('gudang_id', $this->input('source_gudang_id')),
            ],
            'details.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
