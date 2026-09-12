<?php

namespace App\Http\Requests\Search;

use Illuminate\Foundation\Http\FormRequest;

class SearchEmployeeRequest extends FormRequest
{
   public function authorize(): bool { return (bool) $this->user(); }
   public function rules(): array
   {
      return ['search' => ['nullable', 'string', 'max:100'], 'cursor' => ['nullable', 'string']];
   }
}
