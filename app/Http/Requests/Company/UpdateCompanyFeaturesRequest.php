<?php

namespace App\Http\Requests\Company;

use App\Services\RbacService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyFeaturesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('company.settings') ?? false;
    }

    public function rules(): array
    {
        return [
            'features' => ['present', 'array'],
            'features.*' => ['string', Rule::in(RbacService::availableFeatures())],
        ];
    }

    public function messages(): array
    {
        return [
            'features.present' => 'Data fitur wajib dikirim.',
            'features.*.in' => 'Salah satu fitur yang dipilih tidak valid.',
        ];
    }
}
