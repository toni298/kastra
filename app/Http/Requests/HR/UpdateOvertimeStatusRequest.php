<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOvertimeStatusRequest extends FormRequest
{
   public function authorize(): bool { return $this->user()?->can('hr.overtime.approve') ?? false; }
   public function rules(): array { return ['status' => ['required', 'in:approved,rejected']]; }
}
