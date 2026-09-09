<?php

namespace App\Repositories;

use App\Models\Gudang;
use Illuminate\Contracts\Pagination\CursorPaginator;

class GudangRepository extends OrganizationRepository
{
    protected string $model = Gudang::class;

    public function search(string $companyId, array $filters): CursorPaginator
    {
        return Gudang::query()
            ->select(['id', 'nama', 'kode'])
            ->where('company_id', $companyId)
            ->where('aktif', true)
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($nested) => $nested
                ->where('nama', 'like', "%{$search}%")
                ->orWhere('kode', 'like', "%{$search}%")))
            ->orderBy('nama')
            ->orderBy('id')
            ->cursorPaginate(20);
    }
}
