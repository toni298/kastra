<?php

namespace App\Repositories;

use App\Models\Branch;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CabangRepository extends OrganizationRepository
{
    protected string $model = Branch::class;

    public function paginate(string $companyId, array $filters): LengthAwarePaginator
    {
        return Branch::query()
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where(fn ($nested) => $nested
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")))
            ->when(isset($filters['status']), fn ($query) => $query
                ->where(
                    'status',
                    $filters['status'] === 'aktif'
                        ? Branch::STATUS_ACTIVE
                        : Branch::STATUS_INACTIVE
                ))
            ->orderBy($filters['sort'] ?? 'id', $filters['sort_direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function options(string $companyId): Collection
    {
        return Branch::query()
            ->where('company_id', $companyId)
            ->where('status', Branch::STATUS_ACTIVE)
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function search(string $companyId, array $filters): CursorPaginator
    {
        return Branch::query()
            ->where('company_id', $companyId)
            ->where('status', Branch::STATUS_ACTIVE)
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where('name', 'like', "%{$search}%"))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->orderBy('id')
            ->cursorPaginate(20);
    }
}
