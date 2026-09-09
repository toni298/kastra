<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use App\Services\RbacService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class RoleRepository
{
    public function filtered(string $companyId, array $filters, ?array $allowedPermissions = null): LengthAwarePaginator
    {
        $catalog = $allowedPermissions ?? app(RbacService::class)->permissionCatalog();
        $roles = Role::query()
            ->where('company_id', $companyId)
            ->where('name', '!=', 'owner')
            ->withCount('users')
            ->when($filters['search'] ?? null, fn($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 20)
            ->withQueryString();

        $permissions = DB::table(config('permission.table_names.role_has_permissions', 'role_has_permissions'))
            ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
            ->whereIn('role_has_permissions.role_id', $roles->modelKeys())
            ->whereIn('permissions.name', $catalog)
            ->orderBy('permissions.name')
            ->get(['role_has_permissions.role_id', 'permissions.id', 'permissions.name'])
            ->groupBy('role_id')
            ->map(fn(Collection $items): array => $items
                ->map(fn($permission): array => ['id' => $permission->id, 'name' => $permission->name])
                ->all());

        $roles->getCollection()->each(
            fn(Role $role) => $role->setAttribute('permissions', $permissions->get($role->getKey(), []))
        );

        return $roles;
    }

    public function permissions(?array $allowedPermissions = null): Collection
    {
        $catalog = app(RbacService::class)->permissionCatalog();

        $permissionTable = config('permission.table_names.permissions', 'permissions');

        $permissions = collect(Cache::remember('permission-options', now()->addDay(), fn() => DB::table($permissionTable)
            ->whereNotNull('group')
            ->whereIn('name', $catalog)
            ->orderBy('group')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'group', 'subgroup', 'sort_order'])
            ->map(static fn($permission): array => [
                'id' => $permission->id,
                'name' => $permission->name,
                'group' => $permission->group,
                'subgroup' => $permission->subgroup,
                'sort_order' => (int) $permission->sort_order,
            ])
            ->all()));

        return $allowedPermissions === null
            ? $permissions
            : $permissions->whereIn('name', $allowedPermissions)->values();
    }

    public function stats(string $companyId): array
    {
        $roleTable = (new Role())->getTable();
        $modelHasRolesTable = config('permission.table_names.model_has_roles', 'model_has_roles');

        return [
            'roles_count' => Role::query()
                ->where('company_id', $companyId)
                ->where('name', '!=', 'owner')
                ->count(),
            'users_count' => (int) DB::table($modelHasRolesTable)
                ->join($roleTable, "{$roleTable}.id", '=', "{$modelHasRolesTable}.role_id")
                ->where("{$roleTable}.company_id", $companyId)
                ->where("{$roleTable}.name", '!=', 'owner')
                ->where("{$modelHasRolesTable}.model_type", User::class)
                ->selectRaw("COUNT(DISTINCT {$modelHasRolesTable}.model_id) as aggregate")
                ->value('aggregate'),
        ];
    }
}
