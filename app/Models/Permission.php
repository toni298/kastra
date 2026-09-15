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
        $invalidate = static function (): void {
            Cache::forget('permission-options');
            Cache::increment('inertia-auth-version:permissions');
        };

        static::saved($invalidate);
        static::deleted($invalidate);
    }
}

