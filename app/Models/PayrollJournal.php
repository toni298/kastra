<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollJournal extends Model
{
   use UsesUuid;
   protected $fillable = ['payroll_id', 'company_id', 'journal_number', 'transaction_date', 'amount', 'description', 'posted_by'];
   protected function casts(): array { return ['transaction_date' => 'date', 'amount' => 'integer']; }
   public function payroll(): BelongsTo { return $this->belongsTo(Payroll::class); }
}
