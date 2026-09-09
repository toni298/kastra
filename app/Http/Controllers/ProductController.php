<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\IndexProductRequest;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Branch;
use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Services\CompanyContext;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(
        private ProductRepository $repository,
        private ProductService $service,
        private CompanyContext $companyContext,
    ) {}

    public function index(IndexProductRequest $request)
    {
        Gate::authorize('viewAny', Product::class);

        return Inertia::render('Products/Index', $this->productPageProps($request));
    }

    public function cashier(IndexProductRequest $request)
    {
        Gate::authorize('viewAny', Product::class);

        return Inertia::render('Cashier/Product', $this->productPageProps($request));
    }

    private function productPageProps(IndexProductRequest $request): array
    {
        $companyId = $this->companyId();

        return [
            'activeTab' => 'products',
            'products' => fn() => $this->repository
                ->paginate($companyId, $request->validated())
                ->through(fn(Product $product) => ProductResource::make($product)->resolve($request)),
            'filters' => $request->validated(),
            'suggestions' => fn() => $this->service->suggestions($companyId),
            'branches' => fn() => Branch::query()
                ->where('company_id', $companyId)
                ->where('status', Branch::STATUS_ACTIVE)
                ->orderByDesc('is_default')
                ->orderBy('name')
                ->get(['id', 'name']),
        ];
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        Gate::authorize('create', Product::class);
        $this->service->create(
            $this->companyId(),
            (string) $request->user()->getKey(),
            $request->validated(),
        );

        return back()->with('success', 'Produk berhasil dibuat.');
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        Gate::authorize('update', $product);
        $this->service->update(
            $product,
            (string) $request->user()->getKey(),
            $request->validated(),
        );

        return back()->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);
        $this->service->delete($product);

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    private function companyId(): string
    {
        return (string) $this->companyContext->id();
    }
}
