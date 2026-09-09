<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reports\IndexSalesReportRequest;
use App\Http\Resources\SalesTransactionResource;
use App\Repositories\SalesTransactionRepository;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;
use App\Models\SalesTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportsSalesController
{
    public function __construct(
        private SalesTransactionRepository $repository,
        private CompanyContext $companyContext,
    ) {}

    public function index(IndexSalesReportRequest $request): Response
    {
        $filters = array_merge([
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
            'per_page' => 10,
        ], $request->validated());
        $companyId = (string) $this->companyContext->id();

        return Inertia::render('Reports/Sales/Index', [
            'activeTab' => 'sales',
            'cashierLayout' => $request->routeIs('cashier.reports'),
            'reportSalesItems' => fn() => SalesTransactionResource::collection(
                $this->repository->paginateReport($companyId, $filters)
            ),
            'reportSalesSummary' => fn() => $this->repository->reportSummary($companyId, $filters),
            'reportSalesFilters' => $filters,
            'reportSalesOptions' => fn() => [
                'branches' => $this->repository->branches($companyId),
            ],
        ]);
    }

    public function show(Request $request, SalesTransaction $transaction): JsonResponse
    {
        app(InertiaAuthorizationService::class)->authorize($request->user(), 'laporan.view');

        $transaction = $this->repository->findWithDetails(
            (string) $this->companyContext->id(),
            (string) $transaction->getKey(),
        );

        return SalesTransactionResource::make($transaction)->response();
    }

    public function export(IndexSalesReportRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $companyId = (string) $this->companyContext->id();
        $rows = SalesTransactionResource::collection(
            $this->repository->exportReport($companyId, $filters)
        )->resolve($request);
        $summary = $this->repository->reportSummary($companyId, $filters);

        return response()->json([
            'title' => 'Laporan Penjualan',
            'period' => [
                'from' => $filters['date_from'] ?? '-',
                'to' => $filters['date_to'] ?? '-',
            ],
            'columns' => ['Tanggal', 'Faktur', 'Pelanggan', 'Total Qty', 'Total Nominal', 'Pembayaran', 'Status'],
            'rows' => collect($rows)->map(fn(array $row) => [
                $row['date_iso'] ?? '-',
                $row['number'] ?? '-',
                $row['customer'] ?? 'Penjualan Umum',
                (string) $row['total_qty'],
                (int) ($row['total'] ?? 0),
                $row['payment_method'] ?? '-',
                $row['status'] ?? '-',
            ])->values()->all(),
            'summary' => [
                ['Total Penjualan', $summary['revenue']],
                ['Total Transaksi', $summary['transaction_count']],
                ['AOV', $summary['aov']],
                ['Total Item Terjual', $summary['total_qty']],
            ],
            'summaryCurrency' => [true, false, true, false],
        ]);
    }
}