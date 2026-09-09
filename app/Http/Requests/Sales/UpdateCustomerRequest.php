<?php

namespace App\Http\Requests\Sales;

class UpdateCustomerRequest extends StoreCustomerRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('penjualan.customers.edit');
    }
}
