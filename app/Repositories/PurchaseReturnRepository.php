<?php

namespace App\Repositories;

use App\Models\PurchaseReturn;
use Illuminate\Contracts\Pagination\CursorPaginator;

class PurchaseReturnRepository
{
    public function paginate(string $companyId, array $filters): CursorPaginator
    {
        return PurchaseReturn::query()
            ->select(['id', 'company_id', 'purchase_transaction_id', 'supplier_id', 'created_by', 'return_number', 'return_date', 'reason', 'resolution', 'total', 'note'])
            ->with(['supplier:id,name', 'transaction:id,transaction_number', 'creator:id,name'])
            ->withCount('details')
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($nested) => $nested->where('return_number', 'like', "%{$search}%")->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$search}%"))->orWhereHas('transaction', fn ($transaction) => $transaction->where('transaction_number', 'like', "%{$search}%"))))
            ->latest('return_date')->latest('id')
            ->cursorPaginate($filters['per_page'] ?? 10)->withQueryString();
    }

    public function findWithDetails(string $companyId, string $id): PurchaseReturn
    {
        return PurchaseReturn::query()->with(['supplier', 'transaction', 'creator'])->withCount('details')->where('company_id', $companyId)->findOrFail($id);
    }
}
