<?php

namespace App\Repositories;

use App\Models\PurchaseTransaction;
use App\Models\PurchaseTransactionDetail;
use App\Models\Supplier;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Pagination\CursorPaginator;

class PurchaseTransactionRepository
{
    public function paginate(string $companyId, array $filters): CursorPaginator
    {
        return PurchaseTransaction::query()
            ->select(['id', 'company_id', 'supplier_id', 'gudang_id', 'created_by', 'transaction_number', 'document_type', 'transaction_date', 'due_date', 'status', 'payment_status', 'total'])
            ->with(['supplier:id,name', 'gudang:id,nama', 'creator:id,name'])
            ->withCount('details')
            ->where('company_id', $companyId)
            ->when($filters['supplier_id'] ?? null, fn($query, $id) => $query->where('supplier_id', $id))
            ->when($filters['document_type'] ?? null, fn($query, $type) => $query->where('document_type', $type))
            ->when($filters['search'] ?? null, fn($query, $search) => $query->where(fn($nested) => $nested->where('transaction_number', 'like', "%{$search}%")->orWhereHas('supplier', fn($supplier) => $supplier->where('name', 'like', "%{$search}%"))))
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('status', $status))
            ->when($filters['payment_status'] ?? null, fn($query, $status) => $query->where('payment_status', $status))
            ->when($filters['date_from'] ?? null, fn($query, $date) => $query->where('transaction_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($query, $date) => $query->where('transaction_date', '<=', $date))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function suppliers(string $companyId): Collection
    {
        return Supplier::query()->where('company_id', $companyId)->orderBy('name')->get(['id', 'name']);
    }

    public function findWithDetails(string $companyId, string $id): PurchaseTransaction
    {
        return PurchaseTransaction::query()
            ->with(['supplier', 'gudang', 'creator', 'details.product.unit', 'payments.user'])
            ->where('company_id', $companyId)
            ->findOrFail($id);
    }

    public function summaryStats(string $companyId): array
    {
        $monthStart = now()->startOfMonth()->toDateString();

        $activeOrders = (int) PurchaseTransaction::query()
            ->where('company_id', $companyId)
            ->where('document_type', 'purchase_order')
            ->whereNotIn('status', ['completed', 'cancelled', 'closed'])
            ->count();

        $waitingGoods = (int) PurchaseTransaction::query()
            ->where('company_id', $companyId)
            ->where('status', 'ordered')
            ->count();

        $unpaid = PurchaseTransaction::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['ordered', 'received', 'completed'])
            ->where('payment_status', '!=', 'paid')
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total),0) as total')
            ->first();

        $monthStats = PurchaseTransaction::query()
            ->where('company_id', $companyId)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereDate('transaction_date', '>=', $monthStart)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total),0) as total')
            ->first();

        return [
            'active_orders' => $activeOrders,
            'waiting_goods' => $waitingGoods,
            'unpaid_count' => (int) $unpaid->count,
            'unpaid_total' => (int) $unpaid->total,
            'month_total' => (int) $monthStats->total,
            'month_count' => (int) $monthStats->count,
        ];
    }

    public function summaryByDateRange(string $companyId, string $dateFrom, string $dateTo): array
    {
        $stats = PurchaseTransaction::query()
            ->where('company_id', $companyId)
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->whereBetween('transaction_date', [$dateFrom, $dateTo])
            ->selectRaw('COALESCE(SUM(total),0) as total, COUNT(*) as count')
            ->first();

        return [
            'total' => (int) $stats->total,
            'count' => (int) $stats->count,
        ];
    }

    public function paginateReport(string $companyId, array $filters): CursorPaginator
    {
        return $this->reportQuery($companyId, $filters)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function exportReport(string $companyId, array $filters): Collection
    {
        return $this->reportQuery($companyId, $filters)
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();
    }

    public function reportSummary(string $companyId, array $filters): array
    {
        $stats = $this->applyReportFilters(PurchaseTransaction::query(), $companyId, $filters)
            ->selectRaw('COALESCE(SUM(total), 0) as total, COUNT(*) as transaction_count')
            ->first();
        $totalQty = PurchaseTransactionDetail::query()
            ->whereHas('transaction', fn($query) => $this->applyReportFilters($query, $companyId, $filters))
            ->sum('quantity');

        return [
            'total' => (int) $stats->total,
            'transaction_count' => (int) $stats->transaction_count,
            'total_qty' => (int) $totalQty,
        ];
    }

    private function reportQuery(string $companyId, array $filters)
    {
        return $this->applyReportFilters(
            PurchaseTransaction::query()
                ->select(['id', 'company_id', 'supplier_id', 'gudang_id', 'created_by', 'transaction_number', 'document_type', 'transaction_date', 'due_date', 'status', 'payment_status', 'total'])
                ->with(['supplier:id,name', 'gudang:id,nama', 'creator:id,name'])
                ->withSum('details', 'quantity'),
            $companyId,
            $filters,
        );
    }

    private function applyReportFilters($query, string $companyId, array $filters)
    {
        return $query
            ->where('company_id', $companyId)
            ->when($filters['supplier_id'] ?? null, fn($builder, $id) => $builder->where('supplier_id', $id))
            ->when($filters['gudang_id'] ?? null, fn($builder, $id) => $builder->where('gudang_id', $id))
            ->when($filters['status'] ?? null, fn($builder, $status) => $builder->where('status', $status))
            ->when($filters['payment_status'] ?? null, function ($builder, $status) {
                if ($status === 'pending') {
                    return $builder->whereIn('payment_status', ['partial', 'unpaid']);
                }
                if ($status === 'cancelled') {
                    return $builder->where('status', 'cancelled');
                }
                return $builder->where('payment_status', $status);
            })
            ->when($filters['date_from'] ?? null, fn($builder, $date) => $builder->whereDate('transaction_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($builder, $date) => $builder->whereDate('transaction_date', '<=', $date))
            ->when($filters['search'] ?? null, fn($builder, $search) => $builder->where(function ($nested) use ($search) {
                $nested->where('transaction_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn($supplier) => $supplier->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('details.product', fn($product) => $product
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%"));
            }));
    }
}
