<?php

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
   public function up(): void
   {
        $permissions = ['products.view', 'products.create', 'products.edit', 'products.delete', 'products.images', 'product_categories.create', 'product_brands.create', 'units.create'];
      foreach ($permissions as $permission) Permission::findOrCreate($permission, 'web');
      Role::findByName('owner', 'web')->givePermissionTo($permissions);
   }
   public function down(): void
   {
        Permission::query()->whereIn('name', ['products.view', 'products.create', 'products.edit', 'products.delete', 'products.images', 'product_categories.create', 'product_brands.create', 'units.create'])->delete();
   }
};
