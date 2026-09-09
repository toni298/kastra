<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class PurchaseTransaction extends Model
{
    use UsesUuid;
    protected $fillable = ['company_id','supplier_id','gudang_id','created_by','transaction_number','document_type','status','payment_status','transaction_date','due_date','discount','tax','total','note'];
    protected $casts = ['transaction_date'=>'date','due_date'=>'date','discount'=>'integer','tax'=>'integer','total'=>'integer'];
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function gudang(): BelongsTo { return $this->belongsTo(Gudang::class); }
    public function details(): HasMany { return $this->hasMany(PurchaseTransactionDetail::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function returns(): HasMany { return $this->hasMany(PurchaseReturn::class, 'purchase_transaction_id'); }
    public function payments(): HasMany { return $this->hasMany(PurchasePayment::class, 'purchase_transaction_id')->latest('payment_date'); }
}
