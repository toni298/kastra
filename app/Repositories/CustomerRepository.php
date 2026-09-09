<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\CursorPaginator;

class CustomerRepository
{
    public function paginate(string $companyId, array $filters): CursorPaginator
    {
        return Customer::query()
            ->select(['id', 'company_id', 'branch_id', 'name', 'telp', 'address', 'status', 'created_at'])
            ->with('branch:id,name')
            ->withCount('salesTransactions')
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('telp', 'like', "%{$search}%")))
            ->latest('created_at')
            ->latest('id')
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }
}
