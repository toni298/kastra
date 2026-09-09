<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const PERMISSIONS = [
        'product_categories.view',
        'product_categories.edit',
        'product_categories.delete',
        'product_brands.view',
        'product_brands.edit',
        'product_brands.delete',
        'units.view',
        'units.edit',
        'units.delete',
    ];

    public function up(): void
    {
        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::findOrCreate('owner', 'web')->givePermissionTo(self::PERMISSIONS);
    }

    public function down(): void
    {
        Permission::query()->whereIn('name', self::PERMISSIONS)->delete();
    }
};
