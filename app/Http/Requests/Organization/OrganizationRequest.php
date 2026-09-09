<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

abstract class OrganizationRequest extends FormRequest
{
   protected string $module;
   public function authorize(): bool
   {
      $model = $this->route($this->module);
      return $model ? $this->user()->can('update', $model) : $this->user()->can("{$this->module}.create");
   }
   protected function commonRules(): array
   {
      return ['kode' => ['nullable', 'string', 'max:50'], 'nama' => ['required', 'string', 'max:255'], 'email' => ['nullable', 'email', 'max:255'], 'telepon' => ['nullable', 'string', 'max:32'], 'alamat' => ['nullable', 'string', 'max:2000'], 'kota' => ['nullable', 'string', 'max:100'], 'provinsi' => ['nullable', 'string', 'max:100'], 'kode_pos' => ['nullable', 'string', 'max:16'], 'aktif' => ['required', 'boolean'], 'is_store_enabled' => ['sometimes', 'boolean']];
   }
}
