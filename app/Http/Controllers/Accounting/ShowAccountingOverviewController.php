<?php

namespace App\Http\Controllers\Accounting;

use App\Services\AccountingOverviewService;
use App\Services\CompanyContext;
use Inertia\Inertia;
use Inertia\Response;

class ShowAccountingOverviewController
{
    public function __construct(
        private AccountingOverviewService $overviewService,
        private CompanyContext $companyContext,
    ) {}

    public function __invoke(): Response
    {
        $companyId = $this->companyContext->id();

        $overview = $companyId
            ? $this->overviewService->buildOverview($companyId)
            : [
                'summary' => [],
                'health' => [],
                'performance' => ['months' => [], 'revenue' => [], 'expense' => [], 'profit' => []],
                'attention' => [],
                'activities' => [],
                'insights' => [],
            ];

        return Inertia::render('Accounting/Overview', [
            'overview' => $overview,
        ]);
    }
}
