<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    use UsesUuid;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $table = 'branches';

    protected $fillable = [
        'company_id',
        'code',
        'slug',
        'name',
        'email',
        'phone',
        'whatsapp_number',
        'address',
        'city',
        'province',
        'postal_code',
        'status',
        'is_default',
        'is_store_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_store_enabled' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function cashBankAccountSettings(): HasMany
    {
        return $this->hasMany(CashBankAccountSetting::class);
    }
}
