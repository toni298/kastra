<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\IndexStockOpnameRequest;
use App\Http\Requests\Inventory\StoreStockOpnameRequest;
use App\Http\Requests\Inventory\UpdateStockOpnameRequest;
use App\Http\Resources\StockOpnameResource;
use App\Models\StockOpname;
use App\Repositories\StockOpnameRepository;
use App\Services\CompanyContext;
use App\Services\StockOpnameService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class StockOpnameController extends Controller
{
    public function __construct(private StockOpnameRepository $repository, private StockOpnameService $service, private CompanyContext $companyContext) {}

    public function index(IndexStockOpnameRequest $request): Response
    {
        Gate::authorize('viewAny', StockOpname::class);
        $companyId = (string) $this->companyContext->id();
        return Inertia::render(request()->routeIs('cashier.inventory.opnames') ? 'Cashier/Inventory' : 'Inventory/Index', [
            'activeTab' => 'opname',
            'enabledFeatures' => $this->companyContext->current()?->features ?? [],
            'opnameItems' => fn() => StockOpnameResource::collection($this->repository->paginate($companyId, $request->validated())),
            'opnameFilters' => $request->validated(),
            'opnameOptions' => fn() => ['warehouses' => $this->repository->warehouses($companyId), 'branches' => $this->repository->branches($companyId)],
        ]);
    }

    public function store(StoreStockOpnameRequest $request): RedirectResponse
    {
        Gate::authorize('create', StockOpname::class);
        $opname = $this->service->create((string) $this->companyContext->id(), (string) $request->user()->id, $request->validated());
        return redirect()->route('inventory.opname.show', $opname)->with('success', 'Stock opname berhasil dibuat.');
    }

    public function show(Request $request, StockOpname $opname): Response
    {
        Gate::authorize('view', $opname);
        $opname = $this->repository->find((string) $this->companyContext->id(), (string) $opname->getKey());

        return Inertia::render('Inventory/StockOpname', [
            'opname' => StockOpnameResource::make($opname)->resolve($request),
        ]);
    }

    public function detail(Request $request, StockOpname $opname): JsonResponse
    {
        Gate::authorize('view', $opname);

        $opname = $this->repository->find(
            (string) $this->companyContext->id(),
            (string) $opname->getKey(),
        );

        return StockOpnameResource::make($opname)->response();
    }

    public function update(UpdateStockOpnameRequest $request, StockOpname $opname): RedirectResponse
    {
        Gate::authorize('update', $opname);
        $this->service->save($opname, (string) $request->user()->id, $request->validated());
        return back()->with('success', 'Draft stock opname berhasil disimpan.');
    }

    public function complete(UpdateStockOpnameRequest $request, StockOpname $opname): RedirectResponse
    {
        Gate::authorize('complete', $opname);
        $this->service->save($opname, (string) $request->user()->id, $request->validated(), true);
        return redirect()->route('inventory.opnames')->with('success', 'Stock opname berhasil diselesaikan.');
    }

    public function destroy(StockOpname $opname): RedirectResponse
    {
        Gate::authorize('delete', $opname);
        $this->service->delete($opname);

        return back()->with('success', 'Stock opname berhasil dihapus.');
    }
}
