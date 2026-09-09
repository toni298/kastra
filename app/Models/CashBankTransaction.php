<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashBankTransaction extends Model
{
   use UsesUuid;
   protected $fillable = ['company_id', 'cash_bank_account_id', 'created_by', 'transaction_number', 'type', 'category', 'amount', 'transaction_date', 'reference', 'proof_file_path', 'note', 'status'];
   protected $casts = ['amount' => 'integer', 'transaction_date' => 'date'];
   public function account(): BelongsTo
   {
      return $this->belongsTo(CashBankAccount::class, 'cash_bank_account_id');
   }
   public function creator(): BelongsTo
   {
      return $this->belongsTo(User::class, 'created_by');
   }
}
