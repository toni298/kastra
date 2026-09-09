<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use UsesUuid;

    protected $fillable = ['company_id', 'branch_id', 'name', 'address', 'telp', 'status', 'credit_balance'];
    protected $casts = ['credit_balance' => 'integer'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function salesTransactions(): HasMany { return $this->hasMany(SalesTransaction::class); }
}
