<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\PayrollItem;
use App\Models\PayrollJournal;

class Payroll extends Model
{
   use UsesUuid;
   protected $fillable = ['company_id', 'period', 'cutoff_date', 'status', 'total_amount', 'employee_count', 'posted_at', 'posted_by'];
   protected function casts(): array { return ['period' => 'date', 'cutoff_date' => 'date', 'total_amount' => 'integer', 'employee_count' => 'integer', 'posted_at' => 'datetime']; }
   public function items(): HasMany { return $this->hasMany(PayrollItem::class); }
   public function journal(): HasOne { return $this->hasOne(PayrollJournal::class); }
}
