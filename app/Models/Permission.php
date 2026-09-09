<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use UsesUuid;

    protected static function booted(): void
    {
        static::saved(static fn () => Cache::forget('permission-options'));
        static::deleted(static fn () => Cache::forget('permission-options'));
    }
}

