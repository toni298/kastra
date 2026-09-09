<?php

namespace App\Services;

use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;
use App\Models\ChartOfAccount;
use App\Models\PurchaseReturn;
use App\Models\PurchaseTransaction;
use App\Models\SalesReturn;
use App\Models\SalesTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AccountingOverviewService
{
    public function buildOverview(string $companyId): array
    {
        $cacheKey = 'accounting.overview:' . $companyId . ':' . Carbon::now()->format('YmdH');

        return Cache::remember($cacheKey, 300, function () use ($companyId) {
            $now = Carbon::now();
            $startOfMonth = $now->copy()->startOfMonth();

            $monthlyRevenue = $this->getMonthlyRevenueBulk($companyId, $now);
            $monthlyExpense = $this->getMonthlyExpenseBulk($companyId, $now);
            $thisMonthRevenue = $monthlyRevenue[11] ?? 0;
            $prevMonthRevenue = $monthlyRevenue[10] ?? 0;
            $thisMonthExpense = $monthlyExpense[11] ?? 0;
            $prevMonthExpense = $monthlyExpense[10] ?? 0;

            $totalAssets = (int) CashBankAccount::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->sum('current_balance');

            $totalLiabilities = max(0, $this->getTotalLiabilities($companyId));
            $totalEquity = $totalAssets - $totalLiabilities;
            $netProfit = $thisMonthRevenue - $thisMonthExpense;
            $revenueChange = $prevMonthRevenue > 0 ? round((($thisMonthRevenue - $prevMonthRevenue) / $prevMonthRevenue) * 100, 1) : 0;
            $expenseChange = $prevMonthExpense > 0 ? round((($thisMonthExpense - $prevMonthExpense) / $prevMonthExpense) * 100, 1) : 0;
            $profitMargin = $thisMonthRevenue > 0 ? round(($netProfit / $thisMonthRevenue) * 100, 1) : 0;

            $pendingCashTxCount = (int) CashBankTransaction::query()
                ->where('company_id', $companyId)
                ->where('status', 'pending')
                ->count();

            $unpaidPurchaseCount = (int) PurchaseTransaction::query()
                ->where('company_id', $companyId)
                ->whereIn('payment_status', ['unpaid', 'partial'])
                ->count();

            $unpaidSalesCount = (int) SalesTransaction::query()
                ->where('company_id', $companyId)
                ->whereIn('payment_status', ['unpaid', 'partial'])
                ->count();

            $pendingReturns = (int) (PurchaseReturn::query()->where('company_id', $companyId)->where('resolution', 'refund')->count() + SalesReturn::query()->where('company_id', $companyId)->count());

            $hasAccounts = ChartOfAccount::query()->where('company_id', $companyId)->exists();

            $inactiveAccountCount = (int) CashBankAccount::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->whereNotExists(function ($q) use ($startOfMonth) {
                    $q->select(DB::raw(1))
                        ->from('cash_bank_transactions')
                        ->whereColumn('cash_bank_transactions.cash_bank_account_id', 'cash_bank_accounts.id')
                        ->where('transaction_date', '>=', $startOfMonth);
                })
                ->count();

            return [
                'summary' => [
                    ['label' => 'Total Aset', 'value' => $this->formatAmount($totalAssets), 'caption' => 'Kas, piutang, dan aset usaha', 'icon' => 'Landmark', 'color' => 'blue'],
                    ['label' => 'Total Kewajiban', 'value' => $this->formatAmount($totalLiabilities), 'caption' => 'Hutang dan kewajiban berjalan', 'icon' => 'ReceiptText', 'color' => 'amber'],
                    ['label' => 'Modal', 'value' => $this->formatAmount($totalEquity), 'caption' => 'Nilai bersih pemilik', 'icon' => 'WalletCards', 'color' => 'violet'],
                    ['label' => 'Pendapatan Bulan Ini', 'value' => $this->formatAmount($thisMonthRevenue), 'caption' => ($revenueChange >= 0 ? '+' : '') . $revenueChange . '% dari bulan lalu', 'icon' => 'TrendingUp', 'color' => 'emerald'],
                    ['label' => 'Beban Bulan Ini', 'value' => $this->formatAmount($thisMonthExpense), 'caption' => ($expenseChange <= 0 ? 'Turun ' : 'Naik ') . abs($expenseChange) . '% dari bulan lalu', 'icon' => 'TrendingDown', 'color' => 'red'],
                    ['label' => 'Laba Bersih', 'value' => $this->formatAmount($netProfit), 'caption' => $profitMargin . '% dari pendapatan', 'icon' => 'BadgeDollarSign', 'color' => 'cyan'],
                ],
                'health' => $this->buildHealth($pendingCashTxCount, $inactiveAccountCount, $hasAccounts),
                'performance' => $this->buildPerformance($monthlyRevenue, $monthlyExpense, $now),
                'attention' => $this->buildAttention($unpaidPurchaseCount, $unpaidSalesCount, $pendingCashTxCount, $pendingReturns),
                'activities' => $this->buildActivities($companyId),
                'insights' => $this->buildInsights($thisMonthRevenue, $prevMonthRevenue, $thisMonthExpense, $prevMonthExpense, $pendingCashTxCount),
            ];
        });
    }

    private function getMonthlyRevenueBulk(string $companyId, Carbon $now): array
    {
        $startDate = $now->copy()->subMonths(11)->startOfMonth();

        $cashInByMonth = CashBankTransaction::query()
            ->where('company_id', $companyId)
            ->where('type', 'in')
            ->where('transaction_date', '>=', $startDate)
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as ym, SUM(amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $result = [];
        for ($i = 11; $i >= 0; $i--) {
            $ym = $now->copy()->subMonths($i)->format('Y-m');
            $result[] = (int) ($cashInByMonth[$ym] ?? 0);
        }

        return $result;
    }

    private function getMonthlyExpenseBulk(string $companyId, Carbon $now): array
    {
        $startDate = $now->copy()->subMonths(11)->startOfMonth();

        $cashOutByMonth = CashBankTransaction::query()
            ->where('company_id', $companyId)
            ->where('type', 'out')
            ->where('transaction_date', '>=', $startDate)
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m') as ym, SUM(amount) as total")
            ->groupBy('ym')
            ->pluck('total', 'ym');

        $result = [];
        for ($i = 11; $i >= 0; $i--) {
            $ym = $now->copy()->subMonths($i)->format('Y-m');
            $result[] = (int) ($cashOutByMonth[$ym] ?? 0);
        }

        return $result;
    }

    private function getTotalLiabilities(string $companyId): int
    {
        $totalPurchases = (int) PurchaseTransaction::query()
            ->where('company_id', $companyId)
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->sum('total');

        $totalPayments = (int) DB::table('purchase_payments')
            ->where('company_id', $companyId)
            ->sum('amount');

        return $totalPurchases - $totalPayments;
    }

    private function buildHealth(int $pendingCashTxCount, int $inactiveAccountCount, bool $hasAccounts): array
    {
        $health = [];
        $health[] = ['text' => 'Semua jurnal seimbang', 'status' => 'Baik', 'icon' => 'CircleCheck', 'color' => 'emerald'];

        if ($pendingCashTxCount > 0) {
            $health[] = ['text' => $pendingCashTxCount . ' transaksi membutuhkan review', 'status' => 'Perlu ditinjau', 'icon' => 'TriangleAlert', 'color' => 'amber'];
        }

        if ($inactiveAccountCount > 0) {
            $health[] = ['text' => 'Rekonsiliasi bank bulan ini belum selesai', 'status' => 'Belum selesai', 'icon' => 'Landmark', 'color' => 'amber'];
        }

        $health[] = ['text' => $hasAccounts ? 'Chart of accounts telah dikonfigurasi' : 'Chart of accounts belum dikonfigurasi', 'status' => $hasAccounts ? 'Selesai' : 'Perlu disetting', 'icon' => $hasAccounts ? 'LockKeyhole' : 'Settings', 'color' => $hasAccounts ? 'emerald' : 'amber'];

        return $health;
    }

    private function buildPerformance(array $revenue, array $expense, Carbon $now): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $months[] = $now->copy()->subMonths($i)->translatedFormat('M');
        }

        $profit = collect($revenue)->zip($expense)->map(fn($pair) => $pair[0] - $pair[1])->all();

        return compact('months', 'revenue', 'expense', 'profit');
    }

    private function buildAttention(int $unpaidPurchaseCount, int $unpaidSalesCount, int $pendingCashTxCount, int $pendingReturns): array
    {
        $attention = [];

        if ($unpaidPurchaseCount > 0) {
            $attention[] = ['name' => 'Hutang supplier belum dibayar', 'count' => $unpaidPurchaseCount . ' transaksi', 'variant' => 'warning'];
        }
        if ($unpaidSalesCount > 0) {
            $attention[] = ['name' => 'Piutang pelanggan belum tertagih', 'count' => $unpaidSalesCount . ' transaksi', 'variant' => 'warning'];
        }
        if ($pendingCashTxCount > 0) {
            $attention[] = ['name' => 'Transaksi kas menunggu persetujuan', 'count' => $pendingCashTxCount . ' transaksi', 'variant' => 'error'];
        }
        if ($pendingReturns > 0) {
            $attention[] = ['name' => 'Retur perlu ditindaklanjuti', 'count' => $pendingReturns . ' retur', 'variant' => 'info'];
        }

        if (empty($attention)) {
            $attention[] = ['name' => 'Tidak ada item yang membutuhkan perhatian', 'count' => 'Aman', 'variant' => 'success'];
        }

        return $attention;
    }

    private function buildActivities(string $companyId): array
    {
        $activities = collect();

        $sales = SalesTransaction::query()
            ->where('company_id', $companyId)
            ->select('id', 'transaction_number', 'transaction_date', 'total', 'status')
            ->latest('transaction_date')
            ->limit(3)
            ->get();

        foreach ($sales as $sale) {
            $activities->push([
                'title' => 'Penjualan berhasil diposting',
                'reference' => $sale->transaction_number,
                'module' => 'Penjualan',
                'time' => $sale->transaction_date?->format('d/m/Y'),
                'status' => ucfirst($sale->status),
                'variant' => 'success',
                'icon' => 'ShoppingCart',
                'sort_key' => $sale->transaction_date,
            ]);
        }

        $purchases = PurchaseTransaction::query()
            ->where('company_id', $companyId)
            ->select('id', 'transaction_number', 'transaction_date', 'total', 'status')
            ->latest('transaction_date')
            ->limit(3)
            ->get();

        foreach ($purchases as $purchase) {
            $activities->push([
                'title' => 'Pembelian berhasil diposting',
                'reference' => $purchase->transaction_number,
                'module' => 'Pembelian',
                'time' => $purchase->transaction_date?->format('d/m/Y'),
                'status' => ucfirst($purchase->status),
                'variant' => 'success',
                'icon' => 'ShoppingBag',
                'sort_key' => $purchase->transaction_date,
            ]);
        }

        $cashTransactions = CashBankTransaction::query()
            ->where('company_id', $companyId)
            ->select('id', 'transaction_number', 'transaction_date', 'amount', 'type', 'category')
            ->latest('transaction_date')
            ->limit(4)
            ->get();

        foreach ($cashTransactions as $cash) {
            $activities->push([
                'title' => $cash->category ?? 'Transaksi Kas & Bank',
                'reference' => $cash->transaction_number,
                'module' => 'Kas & Bank',
                'time' => $cash->transaction_date?->format('d/m/Y'),
                'status' => ucfirst($cash->type === 'in' ? 'Kas Masuk' : ($cash->type === 'out' ? 'Kas Keluar' : 'Tercatat')),
                'variant' => $cash->type === 'in' ? 'success' : 'info',
                'icon' => 'WalletCards',
                'sort_key' => $cash->transaction_date,
            ]);
        }

        return $activities->sortByDesc('sort_key')->take(5)->values()->map(fn($item) => collect($item)->except('sort_key')->all())->all();
    }

    private function buildInsights(int $thisMonthRevenue, int $prevMonthRevenue, int $thisMonthExpense, int $prevMonthExpense, int $pendingCashTxCount): array
    {
        $insights = [];

        if ($prevMonthRevenue > 0) {
            $change = round((($thisMonthRevenue - $prevMonthRevenue) / $prevMonthRevenue) * 100, 1);
            $insights[] = $change >= 0
                ? "Pendapatan meningkat {$change}% dibanding bulan lalu."
                : "Pendapatan menurun " . abs($change) . "% dibanding bulan lalu.";
        }

        if ($prevMonthExpense > 0) {
            $change = round((($thisMonthExpense - $prevMonthExpense) / $prevMonthExpense) * 100, 1);
            $insights[] = $change <= 0
                ? "Pengeluaran operasional menurun " . abs($change) . "%."
                : "Pengeluaran operasional meningkat {$change}%.";
        }

        $insights[] = $pendingCashTxCount === 0
            ? 'Semua transaksi kas bulan ini berhasil diposting.'
            : "{$pendingCashTxCount} transaksi masih menunggu persetujuan.";

        $insights[] = 'Sistem pencatatan akuntansi berjalan otomatis.';

        return $insights;
    }

    private function formatAmount(int $amount): string
    {
        if ($amount >= 1000000000) {
            return 'Rp ' . number_format($amount / 1000000000, 2, ',', '.') . ' M';
        }
        if ($amount >= 1000000) {
            return 'Rp ' . number_format($amount / 1000000, 1, ',', '.') . ' jt';
        }
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
