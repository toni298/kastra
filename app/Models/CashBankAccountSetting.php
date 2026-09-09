<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashBankAccountSetting extends Model
{
    use UsesUuid;

    protected $fillable = [
        'company_id',
        'cash_bank_account_id',
        'branch_id',
        'is_all_branches',
        'can_receive_money',
        'can_send_money',
        'is_default_receive',
        'is_default_payment',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_all_branches' => 'boolean',
            'can_receive_money' => 'boolean',
            'can_send_money' => 'boolean',
            'is_default_receive' => 'boolean',
            'is_default_payment' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CashBankAccount::class, 'cash_bank_account_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
