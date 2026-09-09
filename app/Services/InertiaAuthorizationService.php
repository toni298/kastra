<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Builds the small authorization snapshot sent to Inertia.
 *
 * This deliberately uses the pivot tables instead of Spatie's relation
 * helpers. The latter return Eloquent Role/Permission instances and are
 * needlessly expensive on every request.
 */
class InertiaAuthorizationService
{
    /** @var array<string, array{roles: list<string>, permissions: list<string>}> */
    private array $snapshots = [];

    public function allows(User $user, string $permission): bool
    {
        return in_array($permission, $this->for($user)['permissions'], true);
    }

    public function authorize(User $user, string $permission): void
    {
        abort_unless($this->allows($user, $permission), 403);
    }

    public function forget(User $user): void
    {
        unset($this->snapshots[$user->getMorphClass() . ':' . $user->getKey()]);
        Cache::forget('inertia-auth:' . $user->getMorphClass() . ':' . $user->getKey());
    }

    /**
     * Invalidate the cached permission snapshot for many users at once.
     * Used when a company's feature set (and therefore role permissions) changes.
     *
     * @param  iterable<int, User>  $users
     */
    public function forgetMany(iterable $users): void
    {
        foreach ($users as $user) {
            if ($user instanceof User) {
                $this->forget($user);
            }
        }
    }

    /** @return array{roles: list<string>, permissions: list<string>} */
    public function for(User $user): array
    {
        $snapshotKey = $user->getMorphClass() . ':' . $user->getKey();

        if (isset($this->snapshots[$snapshotKey])) {
            return $this->snapshots[$snapshotKey];
        }

        $cacheKey = 'inertia-auth:' . $user->getMorphClass() . ':' . $user->getKey();

        return $this->snapshots[$snapshotKey] = Cache::remember($cacheKey, 300, function () use ($user) {
            $modelType = $user->getMorphClass();
            $rolesTable = config('permission.table_names.roles', 'roles');
            $permissionsTable = config('permission.table_names.permissions', 'permissions');
            $modelRoles = config('permission.table_names.model_has_roles', 'model_has_roles');
            $modelPermissions = config('permission.table_names.model_has_permissions', 'model_has_permissions');
            $rolePermissions = config('permission.table_names.role_has_permissions', 'role_has_permissions');

            $roles = DB::table($modelRoles)
                ->join($rolesTable, "{$rolesTable}.id", '=', "{$modelRoles}.role_id")
                ->where("{$modelRoles}.model_id", $user->getKey())
                ->where("{$modelRoles}.model_type", $modelType)
                ->pluck("{$rolesTable}.name")
                ->map(static fn($name): string => (string) $name)
                ->values()
                ->all();

            $direct = DB::table($modelPermissions)
                ->join($permissionsTable, "{$permissionsTable}.id", '=', "{$modelPermissions}.permission_id")
                ->where("{$modelPermissions}.model_id", $user->getKey())
                ->where("{$modelPermissions}.model_type", $modelType)
                ->pluck("{$permissionsTable}.name");

            $fromRoles = DB::table($rolePermissions)
                ->join($permissionsTable, "{$permissionsTable}.id", '=', "{$rolePermissions}.permission_id")
                ->join($modelRoles, "{$modelRoles}.role_id", '=', "{$rolePermissions}.role_id")
                ->where("{$modelRoles}.model_id", $user->getKey())
                ->where("{$modelRoles}.model_type", $modelType)
                ->pluck("{$permissionsTable}.name");

            return [
                'roles' => $roles,
                'permissions' => $direct->merge($fromRoles)->map(static fn($name): string => (string) $name)->unique()->values()->all(),
            ];
        });
    }
}
