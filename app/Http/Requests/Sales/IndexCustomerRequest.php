<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\InertiaAuthorizationService;

class IndexCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(InertiaAuthorizationService::class)->allows($this->user(), 'penjualan.customers.view');
    }
    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100'], 'per_page' => ['nullable', 'integer', 'in:10,25,50,100'], 'cursor' => ['nullable', 'string']];
    }
}
