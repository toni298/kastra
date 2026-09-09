<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashBankTransfer extends Model
{
    use UsesUuid;

    protected $fillable = ['company_id', 'source_account_id', 'destination_account_id', 'created_by', 'transfer_number', 'amount', 'transfer_date', 'reference', 'note'];
    protected $casts = ['amount' => 'integer', 'transfer_date' => 'date'];

    public function sourceAccount(): BelongsTo { return $this->belongsTo(CashBankAccount::class, 'source_account_id'); }
    public function destinationAccount(): BelongsTo { return $this->belongsTo(CashBankAccount::class, 'destination_account_id'); }
}
