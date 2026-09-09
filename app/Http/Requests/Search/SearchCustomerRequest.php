<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\InertiaAuthorizationService;

class SearchCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(InertiaAuthorizationService::class)->allows($this->user(), 'penjualan.transactions.create');
    }
    public function rules(): array
    {
        return ['branch_id' => ['required', 'uuid'], 'search' => ['nullable', 'string', 'max:100']];
    }
}
