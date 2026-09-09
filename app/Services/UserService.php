<?php

namespace App\Services;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function create(string $companyId, array $data): User
    {
        return DB::transaction(function () use ($companyId, $data) {
            $user = User::create([
                ...Arr::except($data, 'role'),
                'company_id' => $companyId,
                'onboarding_completed_at' => now(),
            ]);
            $user->assignRole($this->role($companyId, $data['role']));

            return $user;
        });
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $attributes = Arr::except($data, 'role');
            if (blank($attributes['password'] ?? null)) unset($attributes['password']);
            $user->update($attributes);
            $user->syncRoles([$this->role((string) $user->company_id, $data['role'])]);
            $this->forgetAuthorizationSnapshot($user);

            return $user->refresh();
        });
    }

    public function delete(User $user): void
    {
        DB::transaction(fn() => $user->delete());
    }

    private function role(string $companyId, string $name): Role
    {
        return Role::query()
            ->where('company_id', $companyId)
            ->where('name', $name)
            ->where('guard_name', 'web')
            ->firstOrFail();
    }

    private function forgetAuthorizationSnapshot(User $user): void
    {
        app(InertiaAuthorizationService::class)->forget($user);
    }
}
