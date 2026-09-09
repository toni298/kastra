<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\InertiaAuthorizationService;

class SearchSupplierRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null && app(InertiaAuthorizationService::class)->allows($this->user(), 'pembelian.view'); }
    public function rules(): array { return ['search' => ['nullable', 'string', 'max:100'], 'cursor' => ['nullable', 'string', 'max:1000']]; }
}
