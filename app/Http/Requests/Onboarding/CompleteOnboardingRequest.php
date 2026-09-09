<?php

namespace App\Http\Requests\Onboarding;

use App\Services\RbacService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteOnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->onboarding_completed_at === null;
    }

    public function rules(): array
    {
        return [
            'business_type' => ['required', Rule::in(['perorangan', 'perusahaan'])],
            'business.name' => ['required', 'string', 'max:255'],
            'business.logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'business.legal_name' => ['nullable', 'string', 'max:255'],
            'business.phone' => ['required', 'string', 'max:32'],
            'business.address' => ['required', 'string', 'max:1000'],
            'business.city' => ['required', 'string', 'max:100'],
            'business.province' => ['nullable', 'string', 'max:100'],
            'business.postal_code' => ['nullable', 'string', 'max:16'],
            'has_branches' => ['required', 'boolean'],
            'branches' => ['exclude_if:has_branches,false', 'required', 'array', 'min:1', 'max:20'],
            'branches.*.name' => ['required_if:has_branches,true', 'string', 'max:255'],
            'branches.*.code' => ['required_if:has_branches,true', 'string', 'max:50', 'distinct'],
            'branches.*.address' => ['required_if:has_branches,true', 'string', 'max:1000'],
            'has_warehouses' => ['required', 'boolean'],
            'warehouses' => ['exclude_if:has_warehouses,false', 'required', 'array', 'min:1', 'max:20'],
            'warehouses.*.name' => ['required_if:has_warehouses,true', 'string', 'max:255'],
            'warehouses.*.code' => ['required_if:has_warehouses,true', 'string', 'max:50', 'distinct'],
            'warehouses.*.address' => ['required_if:has_warehouses,true', 'string', 'max:1000'],
            'tax.enabled' => ['required', 'boolean'],
            'tax.rate' => ['required_if:tax.enabled,true', 'nullable', 'numeric', 'min:0', 'max:100'],
            'tax.mode' => ['required_if:tax.enabled,true', 'nullable', Rule::in(['inclusive', 'exclusive'])],
            'tax.npwp' => ['nullable', 'string', 'max:32'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', Rule::in(RbacService::availableFeatures())],
        ];
    }
}
