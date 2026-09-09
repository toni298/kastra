<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class StockOpnameDetail extends Model
{
    use UsesUuid;

    protected $fillable = ['stock_opname_id', 'product_stock_id', 'branch_product_stock_id', 'system_quantity', 'physical_quantity', 'note'];
    protected $casts = ['system_quantity' => 'integer', 'physical_quantity' => 'integer'];

    public function opname(): BelongsTo { return $this->belongsTo(StockOpname::class, 'stock_opname_id'); }
    public function productStock(): BelongsTo { return $this->belongsTo(ProductStock::class); }
    public function branchProductStock(): BelongsTo { return $this->belongsTo(BranchProductStock::class); }
}
