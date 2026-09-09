<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes, UsesUuid;

    protected $fillable = [
        'company_id',
        'name',
        'contact_supplier',
        'email',
        'address',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
    public function purchaseTransactions(): HasMany { return $this->hasMany(PurchaseTransaction::class, 'supplier_id'); }
}
