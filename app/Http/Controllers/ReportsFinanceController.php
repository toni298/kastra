<?php

namespace App\Http\Controllers;

use App\Http\Requests\Reports\IndexFinanceReportRequest;
use App\Http\Resources\CashBankTransactionResource;
use App\Repositories\FinanceReportRepository;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportsFinanceController
{
    public function __construct(
        private FinanceReportRepository $repository,
        private CompanyContext $companyContext,
        private InertiaAuthorizationService $authorization,
    ) {}

    public function index(IndexFinanceReportRequest $request): Response
    {
        $this->authorization->authorize($request->user(), 'laporan.view');
        $filters = array_merge([
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
            'per_page' => 10,
        ], $request->validated());
        $companyId = (string) $this->companyContext->id();

        return Inertia::render('Reports/Finance/FinanceReport', [
            'activeTab' => 'finance',
            'cashierLayout' => $request->routeIs('cashier.reports.finance'),
            'reportFinanceItems' => fn () => CashBankTransactionResource::collection($this->repository->paginate($companyId, $filters)),
            'reportFinanceSummary' => fn () => $this->repository->summary($companyId, $filters),
            'reportFinanceFilters' => $filters,
            'reportFinanceOptions' => fn () => ['accounts' => $this->repository->accounts($companyId)],
        ]);
    }

    public function show(Request $request, string $transaction): JsonResponse
    {
        $this->authorization->authorize($request->user(), 'laporan.view');
        $companyId = (string) $this->companyContext->id();
        $item = $this->repository->find($companyId, $transaction);

        return CashBankTransactionResource::make($item)->response();
    }

    public function export(IndexFinanceReportRequest $request): JsonResponse
    {
        $filters = $request->validated();
        $companyId = (string) $this->companyContext->id();
        $rows = CashBankTransactionResource::collection($this->repository->export($companyId, $filters))->resolve($request);
        $summary = $this->repository->summary($companyId, $filters);

        return response()->json([
            'title' => 'Laporan Keuangan',
            'period' => ['from' => $filters['date_from'] ?? '-', 'to' => $filters['date_to'] ?? '-'],
            'columns' => ['Tanggal Transaksi', 'No. Referensi', 'Akun Kas / Bank', 'Kategori', 'Tipe', 'Nominal', 'Keterangan'],
            'rows' => collect($rows)->map(fn (array $row) => [
                $row['isoDate'] ?? '-', $row['reference'] ?? '-', $row['account'] ?? '-', $row['category'] ?? '-',
                $row['type'] === 'in' ? 'Pemasukan' : ($row['type'] === 'out' ? 'Pengeluaran' : 'Informasi'),
                (int) ($row['amount'] ?? 0), $row['note'] ?? '-',
            ])->values()->all(),
            'summary' => [
                ['Total Pemasukan', $summary['income']], ['Total Pengeluaran', $summary['expense']],
                ['Arus Kas Bersih', $summary['net']], ['Total Saldo Kas & Bank', $summary['current_balance']],
            ],
            'summaryCurrency' => [true, true, true, true],
        ]);
    }
}
