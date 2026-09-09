<?php

namespace App\Repositories;

use App\Models\CashBankTransaction;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Carbon;

class CashBankTransactionRepository
{
    public function paginate(string $companyId, array $filters): CursorPaginator
    {
        return CashBankTransaction::query()
            ->select(['id', 'company_id', 'cash_bank_account_id', 'transaction_number', 'type', 'category', 'amount', 'transaction_date', 'reference', 'note', 'proof_file_path', 'status', 'created_at'])
            ->with('account:id,name')
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn($query, $value) => $query->where(fn($nested) => $nested->where('transaction_number', 'like', "%{$value}%")->orWhere('category', 'like', "%{$value}%")->orWhereHas('account', fn($account) => $account->where('name', 'like', "%{$value}%"))))
            ->when($filters['type'] ?? null, fn($query, $value) => $query->where('type', $value))
            ->when($filters['category'] ?? null, fn($query, $value) => $query->where('category', $value))
            ->when($filters['account_id'] ?? null, fn($query, $value) => $query->where('cash_bank_account_id', $value))
            ->when($filters['date_from'] ?? null, fn($query, $value) => $query->where('transaction_date', '>=', Carbon::parse($value)->startOfDay()))
            ->when($filters['date_to'] ?? null, fn($query, $value) => $query->where('transaction_date', '<', Carbon::parse($value)->addDay()->startOfDay()))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }
}
