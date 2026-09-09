<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class BranchProductStock extends Model
{
    use UsesUuid;

    protected $fillable = ['company_id', 'branch_id', 'product_id', 'quantity', 'discount'];
    protected $casts = ['quantity' => 'integer', 'discount' => 'integer'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
