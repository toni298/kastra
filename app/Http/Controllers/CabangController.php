<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cabang\IndexCabangRequest;
use App\Http\Requests\Cabang\StoreCabangRequest;
use App\Http\Requests\Cabang\UpdateCabangRequest;
use App\Models\Branch;
use App\Repositories\CabangRepository;
use App\Services\CabangService;
use App\Services\CompanyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class CabangController extends Controller
{
    public function __construct(
        private CabangRepository $repository,
        private CabangService $service,
        private CompanyService $companyService,
    ) {}

    public function index(IndexCabangRequest $request): Response
    {
        Gate::authorize('viewAny', Branch::class);
        $company = $this->companyService->ensureForUser($request->user());

        return Inertia::render('Cabang/Index', [
            'items' => $this->repository->paginate($company->id, $request->validated()),
            'filters' => $request->validated(),
        ]);
    }

    public function store(StoreCabangRequest $request): RedirectResponse
    {
        $company = $this->companyService->ensureForUser($request->user());
        $this->service->create($company->id, $request->validated());

        return back()->with('success', 'Cabang berhasil dibuat.');
    }

    public function update(UpdateCabangRequest $request, Branch $cabang): RedirectResponse
    {
        Gate::authorize('update', $cabang);
        $this->service->update($cabang, $request->validated());

        return back()->with('success', 'Cabang berhasil diperbarui.');
    }

    public function destroy(Branch $cabang): RedirectResponse
    {
        Gate::authorize('delete', $cabang);
        $this->service->delete($cabang);

        return back()->with('success', 'Cabang berhasil dihapus.');
    }
}
