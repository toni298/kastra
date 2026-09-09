<?php

namespace App\Http\Requests\Inventory;

use App\Services\InertiaAuthorizationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReceiveTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null
            && app(InertiaAuthorizationService::class)->allows($this->user(), 'inventory.transfers.receive');
    }

    public function rules(): array
    {
        return [
            'details' => ['required', 'array', 'min:1'],
            'details.*.id' => ['required', 'uuid', Rule::exists('transfer_details', 'id')->where('transfer_transaction_id', $this->route('transfer')?->id)],
            'details.*.received_quantity' => ['required', 'integer', 'min:0'],
            'details.*.adjustment_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
