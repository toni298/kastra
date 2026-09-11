<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
   public function toArray(Request $request): array
   {
      return [
         'id' => $this->id,
         'nik' => $this->nik,
         'name' => $this->name,
         'phone' => $this->phone,
         'email' => $this->email,
         'address' => $this->address,
         'role' => $this->role,
         'role_label' => $this->roleLabel(),
         'base_salary' => (int) $this->base_salary,
         'allowance' => (int) $this->allowance,
         'commission_type' => $this->commission_type,
         'commission_value' => (float) $this->commission_value,
         'commission_label' => $this->commission_type === 'percentage'
            ? (float) $this->commission_value . '% Omzet'
            : 'Rp ' . number_format((float) $this->commission_value, 0, ',', '.') . ' / Qty',
         'status' => $this->status,
         'status_label' => $this->status === 'active' ? 'Aktif' : 'Nonaktif',
         'hired_at' => $this->hired_at?->format('Y-m-d'),
         'branch' => $this->whenLoaded('branch', fn() => [
            'id' => $this->branch?->id,
            'name' => $this->branch?->name,
         ]),
      ];
   }

   private function roleLabel(): string
   {
      return [
         'cashier' => 'Kasir',
         'supervisor' => 'Supervisor',
         'staff' => 'Staff',
      ][$this->role] ?? $this->role;
   }
}
