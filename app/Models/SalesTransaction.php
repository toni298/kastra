<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class SalesTransaction extends Model
{
    use UsesUuid;

    protected $fillable = [
        'company_id',
        'branch_id',
        'customer_id',
        'created_by',
        'transaction_number',
        'document_type',
        'order_channel',
        'delivery_method',
        'payment_method',
        'shipping_recipient_name',
        'shipping_phone',
        'shipping_address',
        'shipping_cost',
        'customer_note',
        'transaction_date',
        'due_date',
        'status',
        'payment_status',
        'discount',
        'tax',
        'total',
        'note',
    ];
    protected $casts = [
        'transaction_date' => 'date',
        'due_date' => 'date',
        'discount' => 'integer',
        'tax' => 'integer',
        'shipping_cost' => 'integer',
        'total' => 'integer',
    ];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function details(): HasMany { return $this->hasMany(SalesTransactionDetail::class); }
    public function payment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        // Keep this as an ordered HasOne instead of latestOfMany(). The latter
        // introduces an aggregate subquery whose unqualified foreign key can
        // become ambiguous when sales_payments is joined.
        return $this->hasOne(SalesPayment::class, 'sales_transaction_id', 'id')
            ->orderByDesc('sales_payments.payment_date')
            ->orderByDesc('sales_payments.id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SalesPayment::class, 'sales_transaction_id', 'id')
            ->latest('sales_payments.payment_date');
    }
    public function returns(): HasMany { return $this->hasMany(SalesReturn::class, 'sales_transaction_id'); }
}
