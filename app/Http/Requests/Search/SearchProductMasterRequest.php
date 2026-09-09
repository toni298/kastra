<?php

namespace App\Http\Requests\Search;

use App\Services\InertiaAuthorizationService;
use Illuminate\Foundation\Http\FormRequest;

class SearchProductMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && app(InertiaAuthorizationService::class)->allows($this->user(), 'products.view');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'cursor' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
