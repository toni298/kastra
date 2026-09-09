<?php

namespace App\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class OrganizationRepository
{
    protected string $model;

    public function paginate(string $companyId, array $filters): LengthAwarePaginator
    {
        $model = $this->model;

        return $model::query()
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where(fn ($nested) => $nested
                    ->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%")))
            ->when(isset($filters['status']), fn ($query) => $query
                ->where('aktif', $filters['status'] === 'aktif'))
            ->orderBy($filters['sort'] ?? 'id', $filters['sort_direction'] ?? 'desc')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }
}
