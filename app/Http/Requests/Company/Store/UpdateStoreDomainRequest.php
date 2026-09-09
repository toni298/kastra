<?php

namespace App\Http\Requests\Company\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreDomainRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('store.settings.edit') ?? false;
    }

    public function rules(): array
    {
        $companyId = $this->user()?->company_id;

        return [
            'subdomain' => [
                'nullable', 'string', 'max:63',
                'regex:/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/',
                Rule::unique('store_settings', 'subdomain')->ignore($companyId, 'company_id'),
            ],
            'custom_domain' => [
                'nullable', 'string', 'max:255',
                'regex:/^([a-z0-9-]+\.)+[a-z]{2,}$/',
                Rule::unique('store_settings', 'custom_domain')->ignore($companyId, 'company_id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'subdomain.regex' => 'Subdomain hanya boleh huruf kecil, angka, dan tanda hubung.',
            'custom_domain.regex' => 'Format domain tidak valid (contoh: tokosaya.com).',
        ];
    }
}
