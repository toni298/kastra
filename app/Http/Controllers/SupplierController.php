<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\IndexSupplierRequest;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Repositories\SupplierRepository;
use App\Services\CompanyContext;
use App\Services\SupplierService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SupplierController extends Controller
{
    public function __construct(
        private SupplierRepository $repository,
        private SupplierService $service,
        private CompanyContext $companyContext,
    ) {}

    public function index(IndexSupplierRequest $request): Response
    {
        Gate::authorize('viewAny', Supplier::class);

        return Inertia::render('Suppliers/Index', [
            'items' => fn () => SupplierResource::collection(
                $this->repository->paginate($this->companyId(), $request->validated())
            ),
            'filters' => $request->validated(),
        ]);
    }

    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        Gate::authorize('create', Supplier::class);
        $supplier = $this->service->create($this->companyId(), (string) $request->user()->getKey(), $request->validated());

        return back()->with('success', 'Supplier berhasil dibuat.')->with('supplier', $supplier->only('id', 'name'));
    }

    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        Gate::authorize('update', $supplier);
        $this->service->update($supplier, (string) $request->user()->getKey(), $request->validated());

        return back()->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Request $request, Supplier $supplier): RedirectResponse
    {
        Gate::authorize('delete', $supplier);
        $this->service->delete($supplier, (string) $request->user()->getKey());

        return back()->with('success', 'Supplier berhasil dihapus.');
    }

    private function companyId(): string
    {
        return (string) $this->companyContext->id();
    }
}
