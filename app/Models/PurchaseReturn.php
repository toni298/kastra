<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use UsesUuid;
    protected $fillable = ['company_id','purchase_transaction_id','supplier_id','gudang_id','created_by','return_number','reason','resolution','return_date','total','note'];
    protected $casts = ['return_date'=>'date','total'=>'integer'];
    public function transaction(): BelongsTo { return $this->belongsTo(PurchaseTransaction::class, 'purchase_transaction_id'); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function details(): HasMany { return $this->hasMany(PurchaseReturnDetail::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
