<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('penjualan.customers.create');
    }
    public function rules(): array
    {
        return ['branch_id' => ['nullable', 'uuid', Rule::exists('branches', 'id')->where('company_id', $this->user()->company_id)], 'name' => ['required', 'string', 'max:255'], 'address' => ['nullable', 'string', 'max:1000'], 'telp' => ['nullable', 'string', 'max:32'], 'status' => ['nullable', 'in:active,inactive']];
    }
}
