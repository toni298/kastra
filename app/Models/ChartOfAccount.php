<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use UsesUuid;

    protected $fillable = ['company_id', 'parent_id', 'kode', 'nama', 'kategori', 'is_header', 'is_system', 'aktif'];
    protected $casts = ['is_header' => 'boolean', 'is_system' => 'boolean', 'aktif' => 'boolean'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function parent(): BelongsTo { return $this->belongsTo(self::class, 'parent_id'); }
    public function children(): HasMany { return $this->hasMany(self::class, 'parent_id'); }
}
