<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class SalesTransactionDetail extends Model
{
    use UsesUuid;

    protected $fillable = ['sales_transaction_id', 'product_id', 'quantity', 'unit_price', 'subtotal'];
    protected $casts = ['quantity' => 'integer', 'unit_price' => 'integer', 'subtotal' => 'integer'];

    public function transaction(): BelongsTo { return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
