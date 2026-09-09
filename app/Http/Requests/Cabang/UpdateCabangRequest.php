<?php

namespace App\Http\Requests\Cabang;

use App\Models\Branch;
use Illuminate\Validation\Rule;

class UpdateCabangRequest extends \App\Http\Requests\Organization\OrganizationRequest
{
    protected string $module = 'cabang';

    public function rules(): array
    {
        $rules = $this->commonRules();
        unset($rules['aktif']);

        return [
            ...$rules,
            'kode' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('branches', 'code')
                    ->where('company_id', $this->user()->company_id)
                    ->ignore($this->route('cabang')?->id),
            ],
            'status' => [
                'required',
                Rule::in([
                    Branch::STATUS_ACTIVE,
                    Branch::STATUS_INACTIVE,
                ]),
            ],
        ];
    }
}