<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\InertiaAuthorizationService;

class SearchPurchaseProductRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null && app(InertiaAuthorizationService::class)->allows($this->user(), 'pembelian.view'); }
    public function rules(): array { return ['search' => ['nullable', 'string', 'max:100'], 'gudang_id' => ['nullable', 'uuid'], 'branch_id' => ['nullable', 'uuid'], 'cursor' => ['nullable', 'string', 'max:1000']]; }
}
