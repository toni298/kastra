<?php

namespace App\Http\Requests\Sales;

use App\Services\InertiaAuthorizationService;
use Illuminate\Foundation\Http\FormRequest;

class IndexSalesTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(InertiaAuthorizationService::class)->allows($this->user(), 'penjualan.transactions.view');
    }

    public function rules(): array
    {
        return [
            'tab' => ['nullable', 'in:all,completed,unpaid,draft,overdue'],
            'search' => ['nullable', 'string', 'max:100'],
            'branch_id' => ['nullable', 'uuid'],
            'status' => ['nullable', 'in:draft,completed,return,partial_return,full_return,cancelled'],
            'payment_status' => ['nullable', 'in:unpaid,paid'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'in:10,20,50,100'],
            'sort' => ['nullable', 'in:transaction_number,transaction_date,total'],
            'sort_direction' => ['nullable', 'in:asc,desc'],
        ];
    }
}
