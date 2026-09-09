<?php

namespace App\Repositories;

use App\Models\ChartOfAccount;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ChartOfAccountRepository extends AccountingFoundationRepository
{
    protected string $model = ChartOfAccount::class;

    public function paginate(string $companyId, array $filters): LengthAwarePaginator
    {
        return ChartOfAccount::query()
            ->with('parent:id,kode,nama')
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where(fn ($nested) => $nested
                    ->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%")))
            ->orderBy($filters['sort'] ?? 'kode', $filters['sort_direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function options(string $companyId): Collection
    {
        return ChartOfAccount::query()
            ->where('company_id', $companyId)
            ->where('aktif', true)
            ->orderBy('kode')
            ->get(['id', 'kode', 'nama']);
    }
}
