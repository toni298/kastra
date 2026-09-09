<?php

namespace App\Repositories;

use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;

class CashBankSummaryRepository
{
    public function forCompany(string $companyId): array
    {
        $accounts = CashBankAccount::query()
            ->where('company_id', $companyId)
            ->where('is_active', true);

        $todayTransactions = CashBankTransaction::query()
            ->where('company_id', $companyId)
            ->whereDate('transaction_date', today())
            ->where('status', 'completed');

        return [
            'current_balance' => (int) $accounts->sum('current_balance'),
            'active_accounts_count' => $accounts->count(),
            'today_income' => (int) (clone $todayTransactions)->where('type', 'in')->sum('amount'),
            'today_income_count' => (clone $todayTransactions)->where('type', 'in')->count(),
            'today_expense' => (int) (clone $todayTransactions)->where('type', 'out')->sum('amount'),
            'today_expense_count' => (clone $todayTransactions)->where('type', 'out')->count(),
            'today_transaction_count' => (clone $todayTransactions)->count(),
        ];
    }
}
