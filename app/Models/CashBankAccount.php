<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashBankAccount extends Model
{
    use UsesUuid;
    protected $fillable = ['company_id', 'name', 'type', 'bank_name', 'account_number', 'account_holder', 'currency', 'opening_balance', 'current_balance', 'is_active'];
    protected $casts = ['opening_balance' => 'integer', 'current_balance' => 'integer', 'is_active' => 'boolean'];
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function settings(): HasMany { return $this->hasMany(CashBankAccountSetting::class); }
}
