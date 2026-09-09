<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class PurchasePayment extends Model
{
    use UsesUuid;
    protected $fillable = ['company_id','purchase_transaction_id','created_by','user_id','payment_number','payment_method','amount','payment_date','reference','note'];
    protected $casts = ['amount'=>'integer','payment_date'=>'date'];
    public function transaction(): BelongsTo { return $this->belongsTo(PurchaseTransaction::class, 'purchase_transaction_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class, 'user_id'); }
}
