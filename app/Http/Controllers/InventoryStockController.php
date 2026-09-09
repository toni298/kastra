<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\IndexProductStockRequest;
use App\Http\Resources\ProductStockResource;
use App\Models\ProductStock;
use App\Repositories\ProductStockRepository;
use App\Services\CompanyContext;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class InventoryStockController extends Controller
{
    public function __construct(private ProductStockRepository $repository, private CompanyContext $companyContext) {}

    public function index(IndexProductStockRequest $request): Response
    {
        Gate::authorize('viewAny', ProductStock::class);
        $companyId = (string) $this->companyContext->id();
        $warehouses = $this->repository->warehouses($companyId);

        return Inertia::render(request()->routeIs('cashier.inventory.stock') ? 'Cashier/Inventory' : 'Inventory/Index', [
            'activeTab' => 'stock',
            'stockItems' => fn() => ProductStockResource::collection($this->repository->paginate($companyId, $request->validated())),
            'stockFilters' => $request->validated(),
            'stockOptions' => fn() => ['warehouses' => $warehouses, 'categories' => $this->repository->categories($companyId)],
            'hasMultipleWarehouses' => $warehouses->count() > 1,
        ]);
    }
}
