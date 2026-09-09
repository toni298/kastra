<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class NumberGenerator extends Model
{
    use UsesUuid;

    protected $fillable = ['company_id', 'document_type', 'prefix', 'format', 'reset_period', 'current_sequence', 'sequence_period', 'aktif'];
    protected $casts = ['current_sequence' => 'integer', 'sequence_period' => 'date', 'aktif' => 'boolean'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
}
