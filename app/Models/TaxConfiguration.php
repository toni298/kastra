<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class TaxConfiguration extends Model
{
    use UsesUuid;

    protected $fillable = ['company_id', 'tax_in_account_id', 'tax_out_account_id', 'kode', 'nama', 'jenis', 'persentase', 'mode', 'aktif'];
    protected $casts = ['persentase' => 'decimal:4', 'aktif' => 'boolean'];

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function taxInAccount(): BelongsTo { return $this->belongsTo(ChartOfAccount::class, 'tax_in_account_id'); }
    public function taxOutAccount(): BelongsTo { return $this->belongsTo(ChartOfAccount::class, 'tax_out_account_id'); }
}
