<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\InertiaAuthorizationService;
use Illuminate\Validation\Rule;

class SearchInventoryProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && (
            app(InertiaAuthorizationService::class)->allows($this->user(), 'inventory.stock.create')
            || app(InertiaAuthorizationService::class)->allows($this->user(), 'inventory.stock.edit')
            || app(InertiaAuthorizationService::class)->allows($this->user(), 'inventory.transfers.create')
            || app(InertiaAuthorizationService::class)->allows($this->user(), 'inventory.summary.adjust')
        );
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'cursor' => ['nullable', 'string', 'max:1000'],
            'gudang_id' => ['nullable', 'uuid'],
            'branch_id' => ['nullable', 'uuid', 'exists:branches,id'],
            'selection' => ['nullable', Rule::in(['product'])],
        ];
    }
}
