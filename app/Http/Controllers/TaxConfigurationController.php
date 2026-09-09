<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaxConfiguration\{IndexTaxConfigurationRequest, StoreTaxConfigurationRequest};
use App\Models\TaxConfiguration;
use App\Repositories\{ChartOfAccountRepository, TaxConfigurationRepository};
use App\Services\{CompanyService, TaxConfigurationService};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TaxConfigurationController extends Controller
{
   public function __construct(private TaxConfigurationRepository $repository, private ChartOfAccountRepository $accountRepository, private TaxConfigurationService $service, private CompanyService $companyService) {}
   public function index(IndexTaxConfigurationRequest $request): Response
   {
      $company = $this->companyService->ensureForUser($request->user());
      Gate::authorize('viewAny', TaxConfiguration::class);
      return Inertia::render('Accounting/Taxes/Index', ['items' => $this->repository->paginate($company->id, $request->validated()), 'filters' => $request->validated(), 'options' => $this->accountRepository->options($company->id)]);
   }
   public function store(StoreTaxConfigurationRequest $request): RedirectResponse
   {
      $this->service->create($this->companyService->ensureForUser($request->user())->id, $request->validated());
      return back()->with('success', 'Pajak berhasil dibuat.');
   }
}
