<?php

namespace App\Http\Controllers;

use App\Actions\ReminderCalendarService;
use App\Models\CashBankAccount;
use App\Models\BranchProductStock;
use App\Models\PurchaseTransaction;
use App\Models\SalesTransaction;
use App\Repositories\PurchaseTransactionRepository;
use App\Repositories\SalesTransactionRepository;
use App\Services\CompanyContext;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private SalesTransactionRepository $salesRepo,
        private PurchaseTransactionRepository $purchaseRepo,
        private CompanyContext $companyContext,
        private ReminderCalendarService $reminderCalendarService,
    ) {}

    public function __invoke(Request $request): Response
    {
        $companyId = (string) $this->companyContext->id();

        $dateFrom = $request->query('date_from') ?? now()->startOfMonth()->toDateString();
        $dateTo = $request->query('date_to') ?? now()->toDateString();
        $chartPeriod = $request->query('chart_period', 'month');
        $validPeriods = ['day', 'week', 'month', 'year'];
        $chartPeriod = in_array($chartPeriod, $validPeriods) ? $chartPeriod : 'month';

        $salesSummary = $this->salesRepo->summaryByDateRange($companyId, $dateFrom, $dateTo);
        $purchaseSummary = $this->purchaseRepo->summaryByDateRange($companyId, $dateFrom, $dateTo);
        $chartData = $this->salesRepo->chartDataByDateRange($companyId, $dateFrom, $dateTo);
        $activities = $this->recentActivities($companyId, $dateFrom, $dateTo);
        $lowStock = $this->lowStockProducts($companyId);
        $cashBalance = $this->cashBankBalance($companyId);
        $upcomingReminders = $this->reminderCalendarService->buildUpcoming($companyId, 7, $dateFrom, $dateTo);
        $projectedBills = $this->reminderCalendarService->pendingOutAmountForMonth($companyId, $dateFrom, $dateTo);

        $previousRevenue = $this->previousPeriodRevenue($companyId, $dateFrom, $dateTo);
        $revenueChange = $previousRevenue > 0
            ? round(($salesSummary['revenue'] - $previousRevenue) / $previousRevenue * 100, 1)
            : 0;

        $businessSummary = [
            [
                'label' => 'Penjualan Periode Ini',
                'value' => $salesSummary['revenue'],
                'currency' => true,
                'change' => abs($revenueChange) . '%',
                'direction' => $revenueChange >= 0 ? 'up' : 'down',
                'caption' => 'dari periode sebelumnya',
                'icon' => 'TrendingUp',
                'iconBackground' => 'bg-emerald-50 dark:bg-emerald-900/30',
                'iconColor' => 'text-emerald-600 dark:text-emerald-400',
                'changeColor' => $revenueChange >= 0
                    ? 'text-emerald-600 dark:text-emerald-400'
                    : 'text-red-600 dark:text-red-400',
            ],
            [
                'label' => 'Pembelian Periode Ini',
                'value' => $purchaseSummary['total'],
                'currency' => true,
                'change' => $purchaseSummary['count'] . ' transaksi',
                'direction' => 'up',
                'caption' => 'total pembelian',
                'icon' => 'WalletCards',
                'iconBackground' => 'bg-emerald-50 dark:bg-emerald-900/30',
                'iconColor' => 'text-emerald-600 dark:text-emerald-400',
                'changeColor' => 'text-emerald-600 dark:text-emerald-400',
            ],
            [
                'label' => 'Kas & Bank',
                'value' => $cashBalance,
                'currency' => true,
                'change' => 'Saldo tersedia',
                'direction' => 'up',
                'caption' => 'Proyeksi sisa setelah tagihan bulan ini',
                'projection' => $cashBalance - $projectedBills,
                'icon' => 'Landmark',
                'iconBackground' => 'bg-blue-50 dark:bg-blue-900/30',
                'iconColor' => 'text-blue-600 dark:text-blue-400',
                'changeColor' => 'text-emerald-600 dark:text-emerald-400',
            ],
            [
                'label' => 'Stok Menipis',
                'value' => count($lowStock),
                'currency' => false,
                'suffix' => 'Produk',
                'change' => 'Perlu perhatian',
                'direction' => 'warning',
                'caption' => 'Segera lakukan restock',
                'icon' => 'Package',
                'iconBackground' => 'bg-violet-50 dark:bg-violet-900/30',
                'iconColor' => 'text-violet-600 dark:text-violet-400',
                'changeColor' => 'text-orange-600 dark:text-orange-400',
            ],
        ];

        return Inertia::render('Dashboard', [
            'businessSummary' => $businessSummary,
            'salesChartData' => $chartData,
            'aktivitasTerbaru' => $activities,
            'produkStokMenipis' => $lowStock,
            'upcomingReminders' => $upcomingReminders,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'chartPeriod' => $chartPeriod,
        ]);
    }

    private function recentActivities(string $companyId, string $dateFrom, string $dateTo): array
    {
        $sales = SalesTransaction::query()
            ->with(['customer:id,name'])
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->latest('created_at')
            ->limit(3)
            ->get();

        $purchases = PurchaseTransaction::query()
            ->with(['supplier:id,name'])
            ->where('company_id', $companyId)
            ->whereNotIn('status', ['draft'])
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->latest('created_at')
            ->limit(2)
            ->get();

        $activities = collect();

        foreach ($sales as $t) {
            $activities->push([
                'id' => 'sale-' . $t->id,
                'type' => 'sale',
                'title' => 'Faktur Penjualan #' . $t->transaction_number,
                'subtitle' => 'Pelanggan: ' . ($t->customer?->name ?? 'Penjualan Umum'),
                'amount' => (int) $t->total,
                'status' => $t->payment_status === 'paid' ? 'Lunas' : 'Belum Dibayar',
                'statusColor' => $t->payment_status === 'paid' ? 'green' : 'orange',
                'time' => $t->created_at?->diffForHumans(),
                'link' => route('sales.transactions.index'),
            ]);
        }

        foreach ($purchases as $t) {
            $activities->push([
                'id' => 'purchase-' . $t->id,
                'type' => 'purchase',
                'title' => 'Pembelian #' . $t->transaction_number,
                'subtitle' => 'Supplier: ' . ($t->supplier?->name ?? 'Pembelian Umum'),
                'amount' => (int) $t->total,
                'status' => $t->payment_status === 'paid' ? 'Lunas' : 'Belum Dibayar',
                'statusColor' => $t->payment_status === 'paid' ? 'green' : 'blue',
                'time' => $t->created_at?->diffForHumans(),
                'link' => route('purchases.transactions.index'),
            ]);
        }

        return $activities->sortByDesc(fn($a) => $a['time'])->take(5)->values()->all();
    }

    private function lowStockProducts(string $companyId): array
    {
        return BranchProductStock::query()
            ->join('products', 'products.id', '=', 'branch_product_stocks.product_id')
            ->where('branch_product_stocks.company_id', $companyId)
            ->where('products.is_active', true)
            ->where('products.minimum_stock', '>', 0)
            ->select([
                'products.id',
                'products.name',
                'products.minimum_stock',
            ])
            ->selectRaw('SUM(branch_product_stocks.quantity) as total_stock')
            ->groupBy('products.id', 'products.name', 'products.minimum_stock')
            ->havingRaw('SUM(branch_product_stocks.quantity) <= products.minimum_stock')
            ->orderBy('total_stock')
            ->limit(5)
            ->get()
            ->map(fn($product) => [
                'id' => $product->id,
                'nama' => $product->name,
                'stok' => (int) $product->total_stock,
                'minimum' => (int) $product->minimum_stock,
            ])
            ->all();
    }

    private function cashBankBalance(string $companyId): int
    {
        return (int) CashBankAccount::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->sum('current_balance');
    }

    private function previousPeriodRevenue(string $companyId, string $dateFrom, string $dateTo): int
    {
        $period = CarbonPeriod::create($dateFrom, $dateTo);
        $days = $period->count();
        $prevEnd = now()->parse($dateFrom)->subDay()->toDateString();
        $prevStart = now()->parse($prevEnd)->subDays($days - 1)->toDateString();

        return (int) SalesTransaction::query()
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$prevStart, $prevEnd])
            ->sum('total');
    }
}
