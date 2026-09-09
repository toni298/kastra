<?php

namespace App\Repositories;

use App\Models\NumberGenerator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NumberGeneratorRepository extends AccountingFoundationRepository
{
    protected string $model = NumberGenerator::class;

    public function paginate(string $companyId, array $filters): LengthAwarePaginator
    {
        return NumberGenerator::query()
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where(fn ($nested) => $nested
                    ->where('document_type', 'like', "%{$search}%")
                    ->orWhere('prefix', 'like', "%{$search}%")))
            ->orderBy($filters['sort'] ?? 'document_type', $filters['sort_direction'] ?? 'asc')
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }
}
