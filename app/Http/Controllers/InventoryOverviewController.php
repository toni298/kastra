<?php

namespace App\Http\Controllers;

use App\Repositories\ProductStockRepository;
use App\Models\Branch;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryOverviewController extends Controller
{
    public function __construct(private ProductStockRepository $repository, private CompanyContext $companyContext) {}

    public function index(): Response
    {
        app(InertiaAuthorizationService::class)->authorize(request()->user(), 'inventory.summary.view');

        $companyId = (string) $this->companyContext->id();
        $company = $this->companyContext->current();

        return Inertia::render(request()->routeIs('cashier.inventory') ? 'Cashier/Inventory' : 'Inventory/Index', [
            'activeTab' => 'overview',
            'enabledFeatures' => $company?->features ?? [],
            'overviewSummary' => fn() => $this->repository->dashboardSummary($companyId),
            'overviewBranchStocks' => fn() => $this->repository->branchStockPaginate($companyId, request()->all()),
            'overviewOptions' => fn() => [
                'categories' => $this->repository->categories($companyId),
                'branches' => Branch::query()
                    ->where('company_id', $companyId)
                    ->where('status', Branch::STATUS_ACTIVE)
                    ->orderBy('name')
                    ->get(['id', 'name']),
            ],
        ]);
    }

    public function productDetail(Request $request, string $stockId): JsonResponse
    {
        app(InertiaAuthorizationService::class)->authorize($request->user(), 'inventory.summary.view');

        $companyId = (string) $this->companyContext->id();

        return response()->json(
            $this->repository->productDetail($companyId, $stockId)
        );
    }
}
