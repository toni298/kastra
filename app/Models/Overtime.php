<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Overtime extends Model
{
   use UsesUuid;

   protected $fillable = ['company_id', 'employee_id', 'overtime_date', 'hours', 'hourly_rate', 'total_amount', 'reason', 'status', 'source', 'approved_by', 'approved_at'];

   protected function casts(): array
   {
      return ['overtime_date' => 'date', 'hours' => 'decimal:2', 'hourly_rate' => 'integer', 'total_amount' => 'integer', 'approved_at' => 'datetime'];
   }

   public function company(): BelongsTo { return $this->belongsTo(Company::class); }
   public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
   public function approvedBy(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
}
