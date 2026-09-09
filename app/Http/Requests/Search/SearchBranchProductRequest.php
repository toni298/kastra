<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\InertiaAuthorizationService;
use Illuminate\Validation\Rule;

class SearchBranchProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && (app(InertiaAuthorizationService::class)->allows($this->user(), 'penjualan.transactions.create') || app(InertiaAuthorizationService::class)->allows($this->user(), 'penjualan.transactions.edit'));
    }
    public function rules(): array
    {
        return [
            'branch_id' => ['required', 'uuid', Rule::exists('branches', 'id')->where('company_id', $this->user()->company_id)],
            'search' => ['nullable', 'string', 'max:100'],
        ];
    }
}
