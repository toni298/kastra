<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use UsesUuid;

    protected $fillable = ['company_id', 'name', 'guard_name'];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}

