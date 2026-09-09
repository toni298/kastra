<?php

namespace App\Http\Requests\Purchases;

use Illuminate\Foundation\Http\FormRequest;

class IndexPurchaseTransactionRequest extends FormRequest
{
    // Authorization is performed once in PurchasesTransactionsController@index
    // through the request-scoped InertiaAuthorizationService snapshot.
    public function authorize(): bool
    {
        return $this->user() !== null;
    }
    public function rules(): array
    {
        return ['search' => ['nullable', 'string', 'max:100'], 'supplier_id' => ['nullable', 'uuid'], 'gudang_id' => ['nullable', 'uuid'], 'document_type' => ['nullable', 'in:purchase_order,purchase_invoice'], 'status' => ['nullable', 'in:draft,ordered,received,completed,closed,returned,cancelled'], 'payment_status' => ['nullable', 'in:paid,pending,cancelled'], 'date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date', 'after_or_equal:date_from'], 'cursor' => ['nullable', 'string'], 'per_page' => ['nullable', 'integer', 'in:10,25,50,100']];
    }
}
