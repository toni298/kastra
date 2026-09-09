<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\InertiaAuthorizationService;

class SearchCabangRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($this->user() === null) return false;
        $authorization = app(InertiaAuthorizationService::class);
        return $authorization->allows($this->user(), 'cabang.view') || $authorization->allows($this->user(), 'outlet.create') || $authorization->allows($this->user(), 'outlet.edit');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'cursor' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
