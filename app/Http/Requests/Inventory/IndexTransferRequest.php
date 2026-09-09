<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Services\InertiaAuthorizationService;

class IndexTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(InertiaAuthorizationService::class)->allows($this->user(), 'inventory.transfers.view');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'source_gudang_id' => ['nullable', 'uuid', Rule::exists('gudang', 'id')->where('company_id', $this->user()->company_id)],
            'destination_type' => ['nullable', Rule::in(['gudang', 'branch'])],
            'destination_gudang_id' => ['nullable', 'uuid', Rule::exists('gudang', 'id')->where('company_id', $this->user()->company_id)],
            'destination_branch_id' => ['nullable', 'uuid', Rule::exists('branches', 'id')->where('company_id', $this->user()->company_id)],
            'status' => ['nullable', Rule::in(['in_transit', 'received', 'partially_received', 'cancelled'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ];
    }
}
