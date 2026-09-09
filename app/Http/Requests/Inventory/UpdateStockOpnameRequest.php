<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\InertiaAuthorizationService;
use Illuminate\Validation\Rule;

class UpdateStockOpnameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(InertiaAuthorizationService::class)->allows($this->user(), 'inventory.opname.edit');
    }
    public function rules(): array
    {
        return [
            'status' => ['nullable', 'in:draft,in_progress'],
            'details' => ['required', 'array', 'min:1'],
            'details.*.id' => ['required', 'uuid', Rule::exists('stock_opname_details', 'id')->where('stock_opname_id', $this->route('opname')?->id)],
            'details.*.physical_quantity' => ['nullable', 'integer', 'min:0'],
            'details.*.note' => ['nullable', 'string', 'max:1000'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
