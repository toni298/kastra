<?php

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class IndexStockReportRequest extends FormRequest
{
   public function authorize(): bool
   {
      return $this->user() !== null;
   }

   public function rules(): array
   {
      return [
         'search' => ['nullable', 'string', 'max:100'],
         'category_id' => ['nullable', 'uuid'],
         'brand_id' => ['nullable', 'uuid'],
         'branch_id' => ['nullable', 'uuid'],
         'gudang_id' => ['nullable', 'uuid'],
         'movement_type' => ['nullable', 'in:IN,OUT,ADJUSTMENT,TRANSFER'],
         'date_from' => ['nullable', 'date'],
         'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
         'cursor' => ['nullable', 'string'],
         'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
      ];
   }
}
