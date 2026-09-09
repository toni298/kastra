<?php

namespace App\Http\Requests\Reports;

use App\Services\InertiaAuthorizationService;
use Illuminate\Foundation\Http\FormRequest;

class IndexSalesReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(InertiaAuthorizationService::class)->allows($this->user(), 'laporan.view');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'branch_id' => ['nullable', 'uuid'],
            'payment_method' => ['nullable', 'in:cash,transfer,qris,ewallet'],
            'status' => ['nullable', 'in:draft,completed,return,partial_return,full_return,cancelled'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'in:10,20,50,100'],
        ];
    }
}