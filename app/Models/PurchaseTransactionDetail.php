<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class PurchaseTransactionDetail extends Model
{
    use UsesUuid;
    protected $fillable = ['purchase_transaction_id','product_id','quantity','unit_price','discount','subtotal'];
    protected $casts = ['quantity'=>'integer','unit_price'=>'integer','discount'=>'integer','subtotal'=>'integer'];
    public function transaction(): BelongsTo { return $this->belongsTo(PurchaseTransaction::class, 'purchase_transaction_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
