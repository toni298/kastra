<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use UsesUuid;

    protected $fillable = [
        'company_id',
        'branch_id',
        'gudang_id',
        'product_id',
        'product_sku',
        'product_name',
        'unit_name',
        'location_name',
        'type',
        'movement_type',
        'qty',
        'stock_before',
        'stock_after',
        'reference_number',
        'user_id',
        'user_name',
        'notes',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'stock_before' => 'decimal:3',
        'stock_after' => 'decimal:3',
    ];

    protected static function booted(): void
    {
        static::updating(fn() => throw new \LogicException('Stock movement bersifat immutable.'));
        static::deleting(fn() => throw new \LogicException('Stock movement bersifat immutable.'));
    }
}
