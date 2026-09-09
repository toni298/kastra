<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use UsesUuid;

    protected $fillable = ['company_id', 'source_type', 'gudang_id', 'branch_id', 'created_by', 'completed_by', 'opname_number', 'opname_date', 'status', 'note', 'started_at', 'completed_at'];
    protected $casts = ['opname_date' => 'date', 'started_at' => 'datetime', 'completed_at' => 'datetime'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function gudang(): BelongsTo { return $this->belongsTo(Gudang::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function completer(): BelongsTo { return $this->belongsTo(User::class, 'completed_by'); }
    public function details(): HasMany { return $this->hasMany(StockOpnameDetail::class); }
}
