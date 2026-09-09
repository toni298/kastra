<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnDetail extends Model
{
    use UsesUuid;
    protected $fillable = ['purchase_return_id','product_id','quantity','unit_price','subtotal'];
    protected $casts = ['quantity'=>'integer','unit_price'=>'integer','subtotal'=>'integer'];
    public function return(): BelongsTo { return $this->belongsTo(PurchaseReturn::class, 'purchase_return_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
