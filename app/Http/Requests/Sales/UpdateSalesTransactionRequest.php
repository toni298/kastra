<?php

namespace App\Http\Requests\Sales;

class UpdateSalesTransactionRequest extends StoreSalesTransactionRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('penjualan.transactions.edit');
    }
}
