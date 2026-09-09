<?php

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['name', 'guard_name']);
            $table->foreignUuid('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->cascadeOnDelete();
            $table->unique(['company_id', 'name', 'guard_name']);
        });

        $templates = Role::query()->whereNull('company_id')->with('permissions')->get();

        Company::query()->orderBy('id')->chunk(100, function ($companies) use ($templates): void {
            foreach ($companies as $company) {
                foreach ($templates as $template) {
                    $role = Role::query()->firstOrCreate([
                        'company_id' => $company->id,
                        'name' => $template->name,
                        'guard_name' => $template->guard_name,
                    ]);
                    $role->syncPermissions($template->permissions);
                }
            }
        });

        User::query()->whereNotNull('company_id')->with('roles')->orderBy('id')->chunk(100, function ($users): void {
            foreach ($users as $user) {
                $companyRoles = $user->roles->map(fn (Role $role) => Role::query()
                    ->where('company_id', $user->company_id)
                    ->where('name', $role->name)
                    ->where('guard_name', $role->guard_name)
                    ->firstOrFail());

                $user->syncRoles($companyRoles);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        User::query()->whereNotNull('company_id')->with('roles')->orderBy('id')->chunk(100, function ($users): void {
            foreach ($users as $user) {
                $globalRoles = $user->roles->map(fn (Role $role) => Role::query()
                    ->whereNull('company_id')
                    ->where('name', $role->name)
                    ->where('guard_name', $role->guard_name)
                    ->firstOrFail());

                $user->syncRoles($globalRoles);
            }
        });

        Role::query()->whereNotNull('company_id')->delete();

        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'name', 'guard_name']);
            $table->dropConstrainedForeignId('company_id');
            $table->unique(['name', 'guard_name']);
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
