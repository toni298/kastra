<?php

namespace App\Http\Controllers;

use App\Models\BranchProductStock;
use App\Models\SalesPayment;
use App\Models\SalesReturn;
use App\Models\SalesTransaction;
use App\Models\SalesTransactionDetail;
use App\Services\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CashierDashboardController extends Controller
{
   public function __construct(private CompanyContext $companyContext) {}

   public function __invoke(Request $request): Response
   {
      $companyId = (string) $this->companyContext->id();
      $today = now()->toDateString();
      $period = $request->string('chart_period')->toString() ?: 'today';
      $period = in_array($period, ['today', '7d', '30d', 'month'], true) ? $period : 'today';
      $sales = SalesTransaction::query()
         ->where('company_id', $companyId)
         ->where('status', 'completed')
         ->whereDate('transaction_date', $today);

      return Inertia::render('Cashier/Dashboard', [
         'today' => now()->locale('id')->translatedFormat('d F Y'),
         'summary' => [
            'sales' => (int) (clone $sales)->sum('total'),
            'transactions' => (int) (clone $sales)->count(),
            'unpaid' => (int) (clone $sales)->where('payment_status', 'unpaid')->sum('total'),
            'unpaid_count' => (int) (clone $sales)->where('payment_status', 'unpaid')->count(),
            'returns' => (int) SalesReturn::query()->where('company_id', $companyId)->where('status', 'completed')->whereDate('created_at', $today)->count(),
            'returns_total' => (int) SalesReturn::query()->where('company_id', $companyId)->where('status', 'completed')->whereDate('created_at', $today)->sum('total'),
         ],
         'salesChart' => $this->salesChart($companyId, $period),
         'chartPeriod' => $period,
         'recentTransactions' => (clone $sales)->with('customer:id,name')->latest('created_at')->limit(5)->get(['id', 'transaction_number', 'customer_id', 'total', 'payment_status', 'created_at'])->map(fn(SalesTransaction $transaction) => [
            'id' => $transaction->id,
            'number' => $transaction->transaction_number,
            'customer' => $transaction->customer?->name ?? 'Penjualan Umum',
            'total' => (int) $transaction->total,
            'payment_status' => $transaction->payment_status,
            'time' => $transaction->created_at?->format('d/m/Y H:i'),
         ])->values(),
         'topProducts' => $this->topProducts($companyId, $today),
         'paymentMethods' => $this->paymentMethods($companyId, $today),
         'lowStock' => $this->lowStock($companyId),
      ]);
   }

   private function salesChart(string $companyId, string $period): array
   {
      $query = SalesTransaction::query()
         ->where('company_id', $companyId)
         ->where('status', 'completed');

      if ($period === 'today') {
         return $query
            ->whereDate('transaction_date', now()->toDateString())
            ->selectRaw('HOUR(created_at) as period_key, SUM(total) as total')
            ->groupByRaw('HOUR(created_at)')
            ->orderBy('period_key')
            ->get()
            ->map(fn($row) => ['label' => str_pad((string) $row->period_key, 2, '0', STR_PAD_LEFT) . ':00', 'value' => (int) $row->total])
            ->values()
            ->all();
      }

      $from = $period === '7d' ? now()->subDays(6) : ($period === '30d' ? now()->subDays(29) : now()->startOfMonth());

      return $query
         ->whereBetween('transaction_date', [$from->toDateString(), now()->toDateString()])
         ->selectRaw('DATE(transaction_date) as period_key, SUM(total) as total')
         ->groupByRaw('DATE(transaction_date)')
         ->orderBy('period_key')
         ->get()
         ->map(fn($row) => ['label' => now()->parse($row->period_key)->format('d/m'), 'value' => (int) $row->total])
         ->values()
         ->all();
   }

   private function topProducts(string $companyId, string $today): array
   {
      return SalesTransactionDetail::query()
         ->join('sales_transactions', 'sales_transactions.id', '=', 'sales_transaction_details.sales_transaction_id')
         ->join('products', 'products.id', '=', 'sales_transaction_details.product_id')
         ->where('sales_transactions.company_id', $companyId)
         ->where('sales_transactions.status', 'completed')
         ->whereDate('sales_transactions.transaction_date', $today)
         ->groupBy('products.id', 'products.name')
         ->orderByDesc('sold')
         ->limit(5)
         ->get(['products.id', 'products.name', DB::raw('SUM(sales_transaction_details.quantity) as sold')])
         ->map(fn($product) => ['id' => $product->id, 'name' => $product->name, 'sold' => (int) $product->sold])
         ->values()
         ->all();
   }

   private function paymentMethods(string $companyId, string $today): array
   {
      return SalesPayment::query()
         ->join('sales_transactions', 'sales_transactions.id', '=', 'sales_payments.sales_transaction_id')
         ->where('sales_payments.company_id', $companyId)
         ->whereDate('sales_payments.payment_date', $today)
         ->groupBy('sales_payments.payment_method')
         ->orderByDesc('total')
         ->get(['sales_payments.payment_method', DB::raw('SUM(sales_payments.amount) as total')])
         ->map(fn($payment) => ['method' => $payment->payment_method, 'total' => (int) $payment->total])
         ->values()
         ->all();
   }

   private function lowStock(string $companyId): array
   {
      return BranchProductStock::query()
         ->join('products', 'products.id', '=', 'branch_product_stocks.product_id')
         ->where('branch_product_stocks.company_id', $companyId)
         ->where('products.is_active', true)
         ->groupBy('products.id', 'products.name', 'products.minimum_stock')
         ->havingRaw('SUM(branch_product_stocks.quantity) <= products.minimum_stock')
         ->orderBy('stock')
         ->limit(5)
         ->get(['products.id', 'products.name', 'products.minimum_stock', DB::raw('SUM(branch_product_stocks.quantity) as stock')])
         ->map(fn($product) => ['id' => $product->id, 'name' => $product->name, 'stock' => (int) $product->stock, 'minimum' => (int) $product->minimum_stock])
         ->values()
         ->all();
   }
}
