<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Requests\Accounting\ReportDateRangeRequest;
use App\Services\AccountingReportService;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class ShowAccountingReportDataController
{
    private array $reportMethods = [
        'neraca' => 'balanceSheet',
        'laba-rugi' => 'incomeStatement',
        'arus-kas' => 'cashFlow',
        'buku-besar' => 'generalLedger',
        'neraca-saldo' => 'trialBalance',
        'perubahan-modal' => 'equityChanges',
        'pajak' => 'taxSummary',
    ];

    public function __construct(
        private AccountingReportService $reportService,
        private CompanyContext $companyContext,
    ) {}

    public function __invoke(ReportDateRangeRequest $request, string $report): JsonResponse
    {
        if (!isset($this->reportMethods[$report])) {
            abort(404, 'Laporan tidak ditemukan.');
        }

        $companyId = $this->companyContext->id();
        $from = Carbon::parse($request->validated('date_from'))->startOfDay();
        $to = Carbon::parse($request->validated('date_to'))->endOfDay();
        $method = $this->reportMethods[$report];

        if ($method === 'generalLedger') {
            $cursor = $request->string('cursor')->toString() ?: null;
            return response()->json(
                $this->reportService->generalLedger($companyId, $from, $to, $cursor)
            );
        }

        return response()->json(
            $this->reportService->$method($companyId, $from, $to)
        );
    }
}
