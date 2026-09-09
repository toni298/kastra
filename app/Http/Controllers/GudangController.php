<?php

namespace App\Http\Controllers;

use App\Http\Requests\Gudang\IndexGudangRequest;
use App\Http\Requests\Gudang\StoreGudangRequest;
use App\Http\Requests\Gudang\UpdateGudangRequest;
use App\Models\Gudang;
use App\Repositories\GudangRepository;
use App\Services\CompanyService;
use App\Services\GudangService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class GudangController extends Controller
{
    public function __construct(
        private GudangRepository $repository,
        private GudangService $service,
        private CompanyService $companyService,
    ) {}

    public function index(IndexGudangRequest $request): Response
    {
        Gate::authorize('viewAny', Gudang::class);
        $company = $this->companyService->ensureForUser($request->user());

        return Inertia::render('Gudang/Index', [
            'items' => $this->repository->paginate($company->id, $request->validated()),
            'filters' => $request->validated(),
        ]);
    }

    public function store(StoreGudangRequest $request): RedirectResponse
    {
        $company = $this->companyService->ensureForUser($request->user());
        $this->service->create($company->id, $request->validated());

        return back()->with('success', 'Gudang berhasil dibuat.');
    }

    public function update(UpdateGudangRequest $request, Gudang $gudang): RedirectResponse
    {
        Gate::authorize('update', $gudang);
        $this->service->update($gudang, $request->validated());

        return back()->with('success', 'Gudang berhasil diperbarui.');
    }

    public function destroy(Gudang $gudang): RedirectResponse
    {
        Gate::authorize('delete', $gudang);
        $this->service->delete($gudang);

        return back()->with('success', 'Gudang berhasil dihapus.');
    }
}
