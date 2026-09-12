<?php

namespace App\Http\Requests\Kiosk;

use Illuminate\Foundation\Http\FormRequest;

class ClockRequest extends FormRequest
{
   public function authorize(): bool
   {
      return (bool) $this->user();
   }

   public function rules(): array
   {
      return ['pin' => ['required', 'digits:6']];
   }
}
