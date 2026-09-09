<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SalesPayment extends Model
{
    use UsesUuid;

    protected $fillable = ['company_id', 'branch_id', 'sales_transaction_id', 'payment_number', 'payment_method', 'amount', 'payment_status', 'payment_date', 'reference_number', 'note', 'received_by'];
    protected $casts = ['amount' => 'integer', 'payment_date' => 'date'];
    public function transaction(): BelongsTo { return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id'); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function receiver(): BelongsTo { return $this->belongsTo(User::class, 'received_by'); }
}
