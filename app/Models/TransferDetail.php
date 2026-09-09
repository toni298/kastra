<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class TransferDetail extends Model
{
    use UsesUuid;

    protected $fillable = ['transfer_transaction_id', 'source_stock_id', 'destination_stock_id', 'quantity', 'received_quantity', 'adjustment_note', 'received_at'];
    protected $casts = ['quantity' => 'integer', 'received_quantity' => 'integer', 'received_at' => 'datetime'];

    public function transfer(): BelongsTo { return $this->belongsTo(TransferTransaction::class, 'transfer_transaction_id'); }
    public function sourceStock(): BelongsTo { return $this->belongsTo(ProductStock::class, 'source_stock_id'); }
    public function destinationStock(): BelongsTo { return $this->belongsTo(ProductStock::class, 'destination_stock_id'); }
}
