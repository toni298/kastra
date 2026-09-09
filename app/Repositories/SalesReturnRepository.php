<?php

namespace App\Repositories;

use App\Models\SalesReturn;
use Illuminate\Contracts\Pagination\CursorPaginator;

class SalesReturnRepository
{
    public function paginate(string $companyId, array $filters): CursorPaginator
    {
        return SalesReturn::query()
            ->select(['id', 'company_id', 'branch_id', 'sales_transaction_id', 'created_by', 'return_number', 'reason', 'resolution', 'status', 'total', 'refund_amount', 'replacement_total', 'customer_credit_amount', 'customer_pays_amount', 'created_at'])
            ->with(['branch:id,name', 'transaction:id,customer_id,transaction_number', 'transaction.customer:id,name'])
            ->withCount('details')
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($nested) => $nested
                ->where('return_number', 'like', "%{$search}%")
                ->orWhereHas('transaction', fn ($transaction) => $transaction
                    ->where('transaction_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', "%{$search}%")))))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest('sales_returns.created_at')
            ->orderByDesc('sales_returns.id')
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function findWithDetails(string $companyId, string $id): SalesReturn
    {
        return SalesReturn::query()
            ->with(['branch:id,name', 'transaction:id,customer_id,transaction_number', 'transaction.customer:id,name', 'details:id,sales_return_id,product_id,quantity,unit_price,subtotal', 'details.product:id,name,unit_id', 'details.product.unit:id,name', 'replacements:id,sales_return_id,product_id,quantity,unit_price,subtotal', 'replacements.product:id,name,unit_id', 'replacements.product.unit:id,name'])
            ->where('company_id', $companyId)
            ->findOrFail($id);
    }
}
