<?php

namespace App\Repositories;

use App\Models\TaxConfiguration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TaxConfigurationRepository extends AccountingFoundationRepository
{
    protected string $model = TaxConfiguration::class;

    public function paginate(string $companyId, array $filters): LengthAwarePaginator
    {
        return TaxConfiguration::query()
            ->with(['taxInAccount:id,kode,nama', 'taxOutAccount:id,kode,nama'])
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where(fn ($nested) => $nested
                    ->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%")))
            ->orderBy($filters['sort'] ?? 'kode', $filters['sort_direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }
}
