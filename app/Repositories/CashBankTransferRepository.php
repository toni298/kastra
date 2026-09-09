<?php

namespace App\Repositories;

use App\Models\CashBankTransfer;
use Illuminate\Contracts\Pagination\CursorPaginator;

class CashBankTransferRepository
{
    public function paginate(string $companyId, array $filters): CursorPaginator
    {
        $sort = $filters['sort'] ?? 'transfer_date';
        $direction = $filters['sort_direction'] ?? 'desc';

        return CashBankTransfer::query()
            ->select(['id', 'company_id', 'source_account_id', 'destination_account_id', 'transfer_number', 'amount', 'transfer_date', 'reference'])
            ->with(['sourceAccount:id,name,bank_name', 'destinationAccount:id,name,bank_name'])
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn($query, $search) => $query->where(fn($nested) => $nested->where('transfer_number', 'like', "%{$search}%")->orWhere('reference', 'like', "%{$search}%")))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }
}
