<?php

namespace App\Repositories;

use App\Models\Supplier;
use Illuminate\Contracts\Pagination\CursorPaginator;

class SupplierRepository
{
    public function paginate(string $companyId, array $filters): CursorPaginator
    {
        $sort = $filters['sort'] ?? null;
        $direction = $filters['sort_direction'] ?? 'asc';

        return Supplier::query()
            ->select(['id', 'company_id', 'name', 'contact_supplier', 'email', 'address', 'created_at', 'updated_at'])
            ->withCount('purchaseTransactions')
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn ($query, $search) => $query
                ->where(fn ($nested) => $nested
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_supplier', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")))
            ->when(
                $sort,
                fn ($query) => $query->orderBy($sort, $direction)->orderBy('id', $direction),
                fn ($query) => $query->orderByDesc('created_at')->orderByDesc('id'),
            )
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }
}
