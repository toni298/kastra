<?php

namespace App\Http\Requests\Outlet;

use App\Http\Requests\Organization\OrganizationRequest;
use App\Models\Branch;
use Illuminate\Validation\Rule;

class StoreOutletRequest extends OrganizationRequest
{
    protected string $module = 'outlet';

    public function rules(): array
    {
        $outletId = $this->route('outlet')?->id;

        return [
            ...$this->commonRules(),
            'kode' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('outlet', 'kode')
                    ->where('company_id', $this->user()->company_id)
                    ->ignore($outletId),
            ],
            'cabang_id' => [
                'nullable',
                'uuid',
                Rule::exists(Branch::class, 'id')
                    ->where('company_id', $this->user()->company_id),
            ],
        ];
    }
}