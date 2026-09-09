<?php

namespace App\Http\Requests\Company;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $company = $this->route('company');

        return $this->user()->can('company.edit')
            && (string) $this->user()->company_id === (string) $company->getKey();
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'legal_name' => ['nullable', 'string', 'max:255'], 'npwp' => ['nullable', 'string', 'max:32', 'regex:/^[0-9.\-]+$/'], 'nib' => ['nullable', 'string', 'max:32', 'regex:/^[0-9]+$/'], 'email' => ['nullable', 'email', 'max:255'], 'phone' => ['nullable', 'string', 'max:32'], 'address' => ['nullable', 'string', 'max:2000'], 'city' => ['nullable', 'string', 'max:100'], 'province' => ['nullable', 'string', 'max:100'], 'postal_code' => ['nullable', 'string', 'max:16'], 'country_code' => ['required', 'string', 'size:2']];
    }
}
