<?php

namespace App\Http\Requests\Company\Store;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreAppearanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('store.settings.edit') ?? false;
    }

    public function rules(): array
    {
        return [
            'template' => ['required', Rule::in(['modern', 'minimal'])],
            'primary_color' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{3,8}$/'],
            'secondary_color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{3,8}$/'],
            'tagline' => ['nullable', 'string', 'max:120'],
            'hero_title' => ['nullable', 'string', 'max:120'],
            'hero_subtitle' => ['nullable', 'string', 'max:255'],
            'show_feature_badges' => ['nullable', 'boolean'],
            'is_store_active' => ['nullable', 'boolean'],
            'whatsapp_number' => ['nullable', 'string', 'max:20'],
            'banner' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_banner' => ['nullable', 'boolean'],
            'remove_logo' => ['nullable', 'boolean'],
        ];
    }
}
