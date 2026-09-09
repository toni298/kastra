<?php

namespace App\Repositories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;

class UserRepository
{
    public function paginate(string $companyId, array $filters): CursorPaginator
    {
        $sort = $filters['sort'] ?? 'id';
        $direction = $filters['sort_direction'] ?? 'desc';

        return User::query()
            ->where('company_id', $companyId)
            ->with('roles:id,name')
            ->when($filters['search'] ?? null, fn($query, $search) => $query
                ->where(fn($nested) => $nested
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")))
            ->when($filters['role'] ?? null, fn($query, $role) => $query
                ->whereHas('roles', fn($roleQuery) => $roleQuery->where('name', $role)))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function roles(string $companyId): Collection
    {
        return Role::query()
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}
