<?php

namespace App\Http\Controllers;

use App\Repositories\SalesTransactionRepository;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;
use Inertia\Inertia;
use Inertia\Response;

class SalesOverviewController extends Controller
{
    public function __construct(
        private SalesTransactionRepository $repository,
        private CompanyContext $companyContext,
        private InertiaAuthorizationService $authorization,
    ) {}

    public function index(\Illuminate\Http\Request $request): Response
    {
        $this->authorization->authorize($request->user(), 'penjualan.summary.view');
        $companyId = (string) $this->companyContext->id();
        $period = $request->query('period', 'month');
        $validPeriods = ['day', 'week', 'month', 'year'];
        $period = in_array($period, $validPeriods) ? $period : 'month';

        return Inertia::render('Sales/Overview/Index', [
            'summary' => $this->repository->summaryStats($companyId),
            'chartData' => $this->repository->chartData($companyId, $period),
            'activities' => $this->repository->recentActivities($companyId, 5)->map(fn($t) => [
                'id' => $t->id,
                'number' => $t->transaction_number,
                'customer' => $t->customer?->name ?? 'Penjualan Umum',
                'date' => $t->created_at?->format('d/m/Y H:i'),
                'amount' => (int) $t->total,
                'status' => $t->payment_status === 'paid' ? 'Lunas' : 'Belum Dibayar',
                'variant' => $t->payment_status === 'paid' ? 'success' : 'warning',
                'icon' => $t->payment_status === 'paid' ? 'BadgeCheck' : 'FilePlus2',
                'type' => $t->status === 'completed' ? 'Invoice' : 'Draft',
            ])->values(),
            'topCustomers' => $this->repository->topCustomers($companyId, 5)->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'total_spent' => (int) $c->total_spent,
                'transaction_count' => (int) $c->transaction_count,
            ])->values(),
            'attentionDocs' => $this->repository->attentionDocs($companyId),
        ]);
    }
}
