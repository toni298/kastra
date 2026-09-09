<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class TransferTimeline extends Model
{
    use UsesUuid;

    protected $fillable = ['transfer_transaction_id', 'user_id', 'event', 'note', 'metadata'];
    protected $casts = ['metadata' => 'array'];

    public function transfer(): BelongsTo { return $this->belongsTo(TransferTransaction::class, 'transfer_transaction_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
