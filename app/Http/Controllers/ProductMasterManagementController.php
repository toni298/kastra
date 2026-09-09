<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\ProductMasterRequest;
use App\Http\Resources\ProductMasterResource;
use App\Repositories\ProductMasterRepository;
use App\Services\CompanyContext;
use App\Services\ProductMasterService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ProductMasterManagementController extends Controller
{
    public function __construct(
        private ProductMasterRepository $repository,
        private ProductMasterService $service,
        private CompanyContext $companyContext,
    ) {}

    public function index(ProductMasterRequest $request, string $master): Response
    {
        Gate::authorize('viewAny', $this->repository->modelClass($master));

        return Inertia::render('Products/Index', $this->pageProps($request, $master));
    }

    public function cashier(ProductMasterRequest $request, string $master): Response
    {
        Gate::authorize('viewAny', $this->repository->modelClass($master));

        return Inertia::render('Cashier/Product', $this->pageProps($request, $master));
    }

    private function pageProps(ProductMasterRequest $request, string $master): array
    {
        $companyId = $this->companyId();

        return [
            'activeTab' => $master,
            // The prop name matches the active tab, enabling Inertia partial reloads.
            $master => fn() => (in_array($master, ['product_categories', 'product_brands', 'units'], true)
                ? $this->repository->cursorPaginate($master, $companyId, $request->validated())
                : $this->repository->paginate($master, $companyId, $request->validated()))
                ->through(fn(Model $item) => ProductMasterResource::make($item)->resolve($request)),
            'filters' => $request->validated(),
        ];
    }

    public function store(ProductMasterRequest $request, string $master): RedirectResponse
    {
        Gate::authorize('create', $this->repository->modelClass($master));
        $model = $this->service->createModel($master, $this->companyId(), $request->validated());

        return back()
            ->with('success', 'Data master berhasil ditambahkan.')
            ->with($master, $model->only('id', 'name'));
    }

    public function update(ProductMasterRequest $request, string $master, string $item): RedirectResponse
    {
        $model = $this->repository->find($master, $this->companyId(), $item);
        Gate::authorize('update', $model);
        $this->service->updateModel($model, $request->validated());

        return back()->with('success', 'Data master berhasil diperbarui.');
    }

    public function destroy(ProductMasterRequest $request, string $master, string $item): RedirectResponse
    {
        $model = $this->repository->find($master, $this->companyId(), $item);
        Gate::authorize('delete', $model);
        $this->service->deleteModel($model);

        return back()->with('success', 'Data master berhasil dihapus.');
    }

    private function companyId(): string
    {
        return (string) $this->companyContext->id();
    }
}
