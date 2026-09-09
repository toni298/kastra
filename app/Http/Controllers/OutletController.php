<?php

namespace App\Http\Controllers;

use App\Http\Requests\Outlet\IndexOutletRequest;
use App\Http\Requests\Outlet\StoreOutletRequest;
use App\Http\Requests\Outlet\UpdateOutletRequest;
use App\Models\Outlet;
use App\Repositories\OutletRepository;
use App\Services\CompanyService;
use App\Services\OutletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OutletController extends Controller
{
    public function __construct(
        private OutletRepository $repository,
        private OutletService $service,
        private CompanyService $companyService,
    ) {}

    public function index(IndexOutletRequest $request): Response
    {
        Gate::authorize('viewAny', Outlet::class);
        $company = $this->companyService->ensureForUser($request->user());

        return Inertia::render('Outlet/Index', [
            'items' => $this->repository->paginate($company->id, $request->validated()),
            'filters' => $request->validated(),
        ]);
    }

    public function store(StoreOutletRequest $request): RedirectResponse
    {
        $company = $this->companyService->ensureForUser($request->user());
        $this->service->create($company->id, $request->validated());

        return back()->with('success', 'Outlet berhasil dibuat.');
    }

    public function update(UpdateOutletRequest $request, Outlet $outlet): RedirectResponse
    {
        Gate::authorize('update', $outlet);
        $this->service->update($outlet, $request->validated());

        return back()->with('success', 'Outlet berhasil diperbarui.');
    }

    public function destroy(Outlet $outlet): RedirectResponse
    {
        Gate::authorize('delete', $outlet);
        $this->service->delete($outlet);

        return back()->with('success', 'Outlet berhasil dihapus.');
    }
}
