<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChartOfAccount\{IndexChartOfAccountRequest, StoreChartOfAccountRequest, UpdateChartOfAccountRequest};
use App\Models\ChartOfAccount;
use App\Repositories\ChartOfAccountRepository;
use App\Services\{ChartOfAccountService, CompanyService};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ChartOfAccountController extends Controller
{
   public function __construct(private ChartOfAccountRepository $repository, private ChartOfAccountService $service, private CompanyService $companyService) {}
   public function index(IndexChartOfAccountRequest $request): Response
   {
      $company = $this->companyService->ensureForUser($request->user());
      Gate::authorize('viewAny', ChartOfAccount::class);
      return Inertia::render('Accounting/ChartOfAccounts/Index', ['items' => $this->repository->paginate($company->id, $request->validated()), 'filters' => $request->validated(), 'options' => $this->repository->options($company->id)]);
   }
   public function store(StoreChartOfAccountRequest $request): RedirectResponse
   {
      $this->service->create($this->companyService->ensureForUser($request->user())->id, $request->validated());
      return back()->with('success', 'Akun berhasil dibuat.');
   }
   public function update(UpdateChartOfAccountRequest $request, ChartOfAccount $chart_of_account): RedirectResponse
   {
      Gate::authorize('update', $chart_of_account);
      $this->service->update($chart_of_account, $request->validated());
      return back()->with('success', 'Akun berhasil diperbarui.');
   }
   public function destroy(ChartOfAccount $chart_of_account): RedirectResponse
   {
      Gate::authorize('delete', $chart_of_account);
      $this->service->delete($chart_of_account);
      return back()->with('success', 'Akun berhasil dihapus.');
   }
}
