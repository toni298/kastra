<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
   use UsesUuid;
   protected $fillable = ['payroll_id', 'company_id', 'employee_id', 'basic_salary', 'allowance', 'commission_amount', 'overtime_amount', 'deduction', 'net_salary', 'commission_detail', 'overtime_detail'];
   protected function casts(): array { return ['basic_salary' => 'integer', 'allowance' => 'integer', 'commission_amount' => 'integer', 'overtime_amount' => 'integer', 'deduction' => 'integer', 'net_salary' => 'integer', 'commission_detail' => 'array', 'overtime_detail' => 'array']; }
   public function payroll(): BelongsTo { return $this->belongsTo(Payroll::class); }
   public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
