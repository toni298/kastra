<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\InertiaAuthorizationService;

class IndexSalesReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(InertiaAuthorizationService::class)->allows($this->user(), 'penjualan.returns.view');
    }
    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', 'in:draft,completed,cancelled'], 'per_page' => ['nullable', 'integer', 'in:10,25,50,100']];
    }
}
