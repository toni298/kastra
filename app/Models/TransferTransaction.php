<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class TransferTransaction extends Model
{
    use UsesUuid;

    protected $fillable = ['company_id', 'created_by', 'source_gudang_id', 'destination_gudang_id', 'destination_type', 'destination_branch_id', 'received_by', 'transfer_number', 'transfer_date', 'status', 'workflow_status', 'note', 'dispatched_at', 'received_at'];

    protected $casts = ['transfer_date' => 'date', 'dispatched_at' => 'datetime', 'received_at' => 'datetime'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function sourceGudang(): BelongsTo { return $this->belongsTo(Gudang::class, 'source_gudang_id'); }
    public function destinationGudang(): BelongsTo { return $this->belongsTo(Gudang::class, 'destination_gudang_id'); }
    public function destinationBranch(): BelongsTo { return $this->belongsTo(Branch::class, 'destination_branch_id'); }
    public function receiver(): BelongsTo { return $this->belongsTo(User::class, 'received_by'); }
    public function details(): HasMany { return $this->hasMany(TransferDetail::class); }
    public function timelines(): HasMany { return $this->hasMany(TransferTimeline::class)->latest(); }
}
