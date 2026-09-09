<?php

namespace App\Repositories;

use App\Models\Branch;
use App\Models\SalesTransaction;
use App\Models\Customer;
use App\Models\SalesReturn;
use App\Models\SalesTransactionDetail;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Support\Collection;

class SalesTransactionRepository
{
    public function paginate(string $companyId, array $filters): CursorPaginator
    {
        $sort = $filters['sort'] ?? 'transaction_date';
        $direction = $filters['sort_direction'] ?? 'desc';

        return SalesTransaction::query()
            ->select(['id', 'company_id', 'branch_id', 'customer_id', 'created_by', 'transaction_number', 'document_type', 'transaction_date', 'due_date', 'status', 'payment_status', 'discount', 'tax', 'total'])
            ->with(['branch:id,name', 'customer:id,name', 'creator:id,name'])
            ->withCount('details')
            ->where('company_id', $companyId)
            ->when($filters['tab'] ?? null, function ($query, $tab): void {
                match ($tab) {
                    'completed' => $query->where('status', 'completed'),
                    'unpaid' => $query->where('payment_status', 'unpaid'),
                    'draft' => $query->where('status', 'draft'),
                    'overdue' => $query
                        ->where('payment_status', 'unpaid')
                        ->whereNotNull('due_date')
                        ->whereDate('due_date', '<=', now()->toDateString()),
                    default => null,
                };
            })
            ->when($filters['branch_id'] ?? null, fn($query, $id) => $query->where('branch_id', $id))
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->when($filters['payment_status'] ?? null, fn($query, $status) => $query->where('payment_status', $status))
            ->when($filters['date_from'] ?? null, fn($query, $date) => $query->whereDate('transaction_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($query, $date) => $query->whereDate('transaction_date', '<=', $date))
            ->when($filters['search'] ?? null, fn($query, $search) => $query->where(function ($nested) use ($search) {
                $nested->where('transaction_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($customer) => $customer->where('name', 'like', "%{$search}%"));
            }))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function paginateReport(string $companyId, array $filters): CursorPaginator
    {
        return $this->reportQuery($companyId, $filters)
            ->with('payment')
            ->withSum('details', 'quantity')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function exportReport(string $companyId, array $filters): Collection
    {
        return $this->reportQuery($companyId, $filters)
            ->with('payment')
            ->withSum('details', 'quantity')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();
    }

    public function reportSummary(string $companyId, array $filters): array
    {
        $buildStats = function (array $periodFilters) use ($companyId): array {
            $stats = $this->applyReportFilters(SalesTransaction::query(), $companyId, $periodFilters)
                ->selectRaw('COALESCE(SUM(total), 0) as revenue, COUNT(*) as transaction_count')
                ->first();

            $transactionCount = (int) $stats->transaction_count;
            $totalQty = SalesTransactionDetail::query()
                ->whereHas('transaction', fn($query) => $this->applyReportFilters($query, $companyId, $periodFilters))
                ->sum('quantity');

            return [
                'revenue' => (int) $stats->revenue,
                'transaction_count' => $transactionCount,
                'aov' => $transactionCount > 0 ? (int) round($stats->revenue / $transactionCount) : 0,
                'total_qty' => (float) $totalQty,
            ];
        };

        $current = $buildStats($filters);
        $comparison = null;

        if (($filters['date_from'] ?? null) && ($filters['date_to'] ?? null)) {
            $dateFrom = \Carbon\Carbon::parse($filters['date_from']);
            $dateTo = \Carbon\Carbon::parse($filters['date_to']);
            $periodDays = $dateFrom->diffInDays($dateTo) + 1;
            $previousTo = $dateFrom->copy()->subDay();
            $previousFrom = $previousTo->copy()->subDays($periodDays - 1);
            $previousFilters = array_merge($filters, [
                'date_from' => $previousFrom->toDateString(),
                'date_to' => $previousTo->toDateString(),
            ]);
            $previous = $buildStats($previousFilters);
            $change = static fn($value, $previousValue) => $previousValue > 0
                ? round(($value - $previousValue) / $previousValue * 100, 1)
                : null;

            $comparison = [
                'revenue' => $change($current['revenue'], $previous['revenue']),
                'transaction_count' => $change($current['transaction_count'], $previous['transaction_count']),
                'aov' => $change($current['aov'], $previous['aov']),
                'total_qty' => $change($current['total_qty'], $previous['total_qty']),
            ];
        }

        return [
            ...$current,
            'comparison' => $comparison,
        ];
    }

    public function findWithDetails(string $companyId, string $id): SalesTransaction
    {
        return SalesTransaction::query()
            ->with(['branch:id,name,address,city,province,postal_code,phone,email', 'customer:id,name,telp,address', 'creator:id,name', 'details:id,sales_transaction_id,product_id,quantity,unit_price,subtotal', 'details.product:id,name,sku,unit_id', 'details.product.unit:id,name', 'payments', 'returns.details'])
            ->where('company_id', $companyId)
            ->findOrFail($id);
    }

    public function branches(string $companyId): Collection
    {
        return Branch::query()->where('company_id', $companyId)->where('status', Branch::STATUS_ACTIVE)->orderBy('name')->get(['id', 'name']);
    }

    private function reportQuery(string $companyId, array $filters)
    {
        return $this->applyReportFilters(
            SalesTransaction::query()
                ->select([
                    'id',
                    'company_id',
                    'branch_id',
                    'customer_id',
                    'created_by',
                    'transaction_number',
                    'document_type',
                    'transaction_date',
                    'due_date',
                    'status',
                    'payment_status',
                    'payment_method',
                    'discount',
                    'tax',
                    'total',
                ])
                ->with(['branch:id,name', 'customer:id,name', 'creator:id,name']),
            $companyId,
            $filters,
        );
    }

    private function applyReportFilters($query, string $companyId, array $filters)
    {
        return $query
            ->where('company_id', $companyId)
            ->when($filters['branch_id'] ?? null, fn($builder, $branchId) => $builder->where('branch_id', $branchId))
            ->when($filters['status'] ?? null, fn($builder, $status) => $builder->where('status', $status))
            ->when($filters['payment_method'] ?? null, fn($builder, $method) => $builder->where(function ($nested) use ($method) {
                $nested->where('payment_method', $method)
                    ->orWhereHas('payment', fn($payment) => $payment->where('payment_method', $method));
            }))
            ->when($filters['date_from'] ?? null, fn($builder, $date) => $builder->whereDate('transaction_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($builder, $date) => $builder->whereDate('transaction_date', '<=', $date))
            ->when($filters['search'] ?? null, fn($builder, $search) => $builder->where(function ($nested) use ($search) {
                $nested->where('transaction_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn($customer) => $customer->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('details.product', fn($product) => $product
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%"));
            }));
    }

    public function customers(string $companyId, string $branchId, ?string $search = null): Collection
    {
        return Customer::query()->where('company_id', $companyId)->where(fn($query) => $query->whereNull('branch_id')->orWhere('branch_id', $branchId))->where('status', 'active')->when($search, fn($query, $value) => $query->where('name', 'like', "%{$value}%"))->orderBy('name')->limit(30)->get(['id', 'name', 'telp']);
    }

    public function summaryStats(string $companyId): array
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $todayStats = SalesTransaction::query()
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', $today)
            ->selectRaw('COALESCE(SUM(total),0) as total, COUNT(*) as count')
            ->first();

        $monthStats = SalesTransaction::query()
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', '>=', $monthStart)
            ->selectRaw('COALESCE(SUM(total),0) as total, COUNT(*) as count')
            ->first();

        $unpaidStats = SalesTransaction::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['completed', 'partial_return'])
            ->where('payment_status', 'unpaid')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total),0) as total')
            ->first();

        $newCustomers = Customer::query()->where('company_id', $companyId)->whereDate('created_at', '>=', $monthStart)->count();

        $returnStats = SalesReturn::query()
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereDate('created_at', '>=', $monthStart)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total),0) as total')
            ->first();

        $lastMonthStart = now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthEnd = now()->subMonth()->endOfMonth()->toDateString();
        $lastMonthRevenue = (int) SalesTransaction::query()
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$lastMonthStart, $lastMonthEnd])
            ->sum('total');

        $yesterdayRevenue = (int) SalesTransaction::query()
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', now()->subDay()->toDateString())
            ->sum('total');

        return [
            'today_revenue' => (int) $todayStats->total,
            'today_count' => (int) $todayStats->count,
            'month_revenue' => (int) $monthStats->total,
            'month_count' => (int) $monthStats->count,
            'unpaid_count' => (int) $unpaidStats->count,
            'unpaid_total' => (int) $unpaidStats->total,
            'new_customers' => $newCustomers,
            'return_count' => (int) $returnStats->count,
            'return_total' => (int) $returnStats->total,
            'today_compare' => $yesterdayRevenue > 0 ? round(((int) $todayStats->total - $yesterdayRevenue) / $yesterdayRevenue * 100, 1) : 0,
            'month_compare' => $lastMonthRevenue > 0 ? round(((int) $monthStats->total - $lastMonthRevenue) / $lastMonthRevenue * 100, 1) : 0,
        ];
    }

    public function chartData(string $companyId, string $period = 'month'): array
    {
        $config = match ($period) {
            'day' => ['format' => '%Y-%m-%d', 'days' => 7],
            'week' => ['format' => '%Y-%m-%d', 'days' => 28],
            'year' => ['format' => '%Y-%m', 'days' => 365],
            default => ['format' => '%Y-%m-%d', 'days' => 30],
        };

        $startDate = now()->subDays($config['days'])->toDateString();

        $rows = SalesTransaction::query()
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereDate('transaction_date', '>=', $startDate)
            ->selectRaw("DATE_FORMAT(transaction_date, '{$config['format']}') as period_label, COUNT(*) as count, COALESCE(SUM(total),0) as total")
            ->groupBy('period_label')
            ->orderBy('period_label')
            ->get();

        return $rows->map(function ($row) use ($period) {
            $label = $row->period_label;
            $date = $row->period_label;

            if ($period === 'year') {
                $carbon = \Carbon\Carbon::createFromFormat('Y-m', $row->period_label)->locale('id');
                $label = $carbon->translatedFormat('M');
                $date = $carbon->translatedFormat('F Y');
            } elseif (strlen($row->period_label) >= 10) {
                $carbon = \Carbon\Carbon::createFromFormat('Y-m-d', substr($row->period_label, 0, 10))->locale('id');
                $label = $carbon->translatedFormat('j M');
                $date = $carbon->translatedFormat('d F Y');
            }

            return [
                'label' => $label,
                'date' => $date,
                'value' => (int) $row->total,
                'count' => (int) $row->count,
            ];
        })->all();
    }

    public function chartDataByDateRange(string $companyId, string $dateFrom, string $dateTo): array
    {
        $carbonFrom = \Carbon\Carbon::parse($dateFrom);
        $carbonTo = \Carbon\Carbon::parse($dateTo);
        $diffDays = $carbonFrom->diffInDays($carbonTo);

        $format = $diffDays <= 31 ? '%Y-%m-%d' : '%Y-%m';

        $rows = SalesTransaction::query()
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->selectRaw("DATE_FORMAT(transaction_date, '{$format}') as period_label, COUNT(*) as count, COALESCE(SUM(total),0) as total")
            ->groupBy('period_label')
            ->orderBy('period_label')
            ->get();

        return $rows->map(function ($row) use ($format) {
            $label = $row->period_label;
            $date = $row->period_label;

            if ($format === '%Y-%m') {
                $carbon = \Carbon\Carbon::createFromFormat('Y-m', $row->period_label)->locale('id');
                $label = $carbon->translatedFormat('M');
                $date = $carbon->translatedFormat('F Y');
            } else {
                $carbon = \Carbon\Carbon::createFromFormat('Y-m-d', substr($row->period_label, 0, 10))->locale('id');
                $label = $carbon->translatedFormat('j M');
                $date = $carbon->translatedFormat('d F Y');
            }

            return [
                'label' => $label,
                'date' => $date,
                'value' => (int) $row->total,
                'count' => (int) $row->count,
            ];
        })->all();
    }

    public function summaryByDateRange(string $companyId, string $dateFrom, string $dateTo): array
    {
        $stats = SalesTransaction::query()
            ->where('company_id', $companyId)
            ->where('status', 'completed')
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->selectRaw('COALESCE(SUM(total),0) as total, COUNT(*) as count')
            ->first();

        $unpaidStats = SalesTransaction::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['completed', 'partial_return'])
            ->where('payment_status', 'unpaid')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total),0) as total')
            ->first();

        return [
            'revenue' => (int) $stats->total,
            'count' => (int) $stats->count,
            'unpaid_count' => (int) $unpaidStats->count,
            'unpaid_total' => (int) $unpaidStats->total,
        ];
    }

    public function recentActivities(string $companyId, int $limit = 5): Collection
    {
        return SalesTransaction::query()
            ->with(['customer:id,name', 'creator:id,name'])
            ->where('company_id', $companyId)
            ->latest('created_at')
            ->limit($limit)
            ->get(['id', 'transaction_number', 'customer_id', 'created_by', 'status', 'payment_status', 'total', 'transaction_date', 'created_at']);
    }

    public function topCustomers(string $companyId, int $limit = 5): Collection
    {
        return Customer::query()
            ->select('customers.id', 'customers.name')
            ->selectRaw('COALESCE(SUM(sales_transactions.total),0) as total_spent, COUNT(sales_transactions.id) as transaction_count')
            ->leftJoin('sales_transactions', function ($join) {
                $join->on('customers.id', '=', 'sales_transactions.customer_id')
                    ->where('sales_transactions.status', '=', 'completed');
            })
            ->where('customers.company_id', $companyId)
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();
    }

    public function attentionDocs(string $companyId): array
    {
        $unpaid = SalesTransaction::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['completed', 'partial_return'])
            ->where('payment_status', 'unpaid')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total),0) as total')
            ->first();

        $returns = SalesReturn::query()
            ->where('company_id', $companyId)
            ->where('status', 'draft')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total),0) as total')
            ->first();

        return [
            ['name' => 'Invoice belum dibayar', 'count' => (int) $unpaid->count, 'value' => (int) $unpaid->total, 'variant' => 'warning'],
            ['name' => 'Draft retur menunggu', 'count' => (int) $returns->count, 'value' => (int) $returns->total, 'variant' => 'info'],
        ];
    }
}
