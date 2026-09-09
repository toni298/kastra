<?php

namespace App\Repositories;

use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;

class FinanceReportRepository
{
    public function paginate(string $companyId, array $filters): CursorPaginator
    {
        return $this->query($companyId, $filters)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function export(string $companyId, array $filters): Collection
    {
        return $this->query($companyId, $filters)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();
    }

    public function summary(string $companyId, array $filters): array
    {
        $completed = $this->applyFilters(CashBankTransaction::query(), $companyId, $filters)
            ->where('status', 'completed');
        $income = (int) (clone $completed)->where('type', 'in')->sum('amount');
        $expense = (int) (clone $completed)->where('type', 'out')->sum('amount');

        return [
            'income' => $income,
            'expense' => $expense,
            'net' => $income - $expense,
            'current_balance' => (int) CashBankAccount::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->sum('current_balance'),
            'income_categories' => $this->categories($completed, 'in', $income),
            'expense_categories' => $this->categories($completed, 'out', $expense),
        ];
    }

    public function accounts(string $companyId): Collection
    {
        return CashBankAccount::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'bank_name', 'current_balance']);
    }

    public function find(string $companyId, string $id): CashBankTransaction
    {
        return CashBankTransaction::query()
            ->with('account:id,name,bank_name')
            ->where('company_id', $companyId)
            ->findOrFail($id);
    }

    private function categories($query, string $type, int $total): array
    {
        return (clone $query)
            ->where('type', $type)
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'label' => $row->category,
                'total' => (int) $row->total,
                'percentage' => $total > 0 ? round(((int) $row->total / $total) * 100, 1) : 0,
            ])
            ->values()
            ->all();
    }

    private function query(string $companyId, array $filters)
    {
        return $this->applyFilters(
            CashBankTransaction::query()
                ->select(['id', 'company_id', 'cash_bank_account_id', 'transaction_number', 'type', 'category', 'amount', 'transaction_date', 'reference', 'note', 'status', 'created_at'])
                ->with('account:id,name,bank_name'),
            $companyId,
            $filters,
        );
    }

    private function applyFilters($query, string $companyId, array $filters)
    {
        return $query
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn ($builder, $search) => $builder->where(function ($nested) use ($search) {
                $nested->where('transaction_number', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhereHas('account', fn ($account) => $account->where('name', 'like', "%{$search}%"));
            }))
            ->when($filters['account_id'] ?? null, fn ($builder, $id) => $builder->where('cash_bank_account_id', $id))
            ->when($filters['type'] ?? null, fn ($builder, $type) => $builder->where('type', $type))
            ->when($filters['category'] ?? null, fn ($builder, $category) => $builder->where('category', $category))
            ->when($filters['date_from'] ?? null, fn ($builder, $date) => $builder->whereDate('transaction_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($builder, $date) => $builder->whereDate('transaction_date', '<=', $date));
    }
}
