<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = $this->route('company');

        return $this->user()->can('company.settings') && (string) $this->user()->company_id === (string) $company->getKey();
    }

    public function rules(): array
    {
        return [
            'currency_code' => ['required', 'string', 'size:3', 'uppercase'],
            'timezone' => ['required', 'string', Rule::in(timezone_identifiers_list())],
            'locale' => ['required', 'string', Rule::in(['id', 'en'])],
        ];
    }
}

