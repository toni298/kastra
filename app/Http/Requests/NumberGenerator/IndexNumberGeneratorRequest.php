<?php

namespace App\Http\Requests\NumberGenerator;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexNumberGeneratorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('number_generators.view');
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
            'sort' => ['nullable', Rule::in(['document_type', 'prefix', 'aktif'])],
            'sort_direction' => ['nullable', Rule::in(['asc', 'desc'])],
        ];
    }
}
