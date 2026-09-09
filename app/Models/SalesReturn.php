<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use UsesUuid;
    protected $fillable = ['company_id', 'branch_id', 'sales_transaction_id', 'created_by', 'return_number', 'reason', 'resolution', 'status', 'note', 'total', 'refund_amount', 'replacement_total', 'customer_credit_amount', 'customer_pays_amount'];
    protected $casts = ['total' => 'integer', 'refund_amount' => 'integer', 'replacement_total' => 'integer', 'customer_credit_amount' => 'integer', 'customer_pays_amount' => 'integer'];
    public function transaction(): BelongsTo { return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id'); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function details(): HasMany { return $this->hasMany(SalesReturnDetail::class); }
    public function replacements(): HasMany { return $this->hasMany(SalesReturnReplacement::class); }
}
