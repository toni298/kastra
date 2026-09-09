<?php

namespace App\Http\Controllers;

use App\Http\Requests\NumberGenerator\{IndexNumberGeneratorRequest, StoreNumberGeneratorRequest};
use App\Models\NumberGenerator;
use App\Repositories\NumberGeneratorRepository;
use App\Services\{CompanyService, NumberGeneratorService};
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class NumberGeneratorController extends Controller
{
   public function __construct(private NumberGeneratorRepository $repository, private NumberGeneratorService $service, private CompanyService $companyService) {}
   public function index(IndexNumberGeneratorRequest $request): Response
   {
      $company = $this->companyService->ensureForUser($request->user());
      Gate::authorize('viewAny', NumberGenerator::class);
      return Inertia::render('Accounting/NumberGenerators/Index', ['items' => $this->repository->paginate($company->id, $request->validated()), 'filters' => $request->validated()]);
   }
   public function store(StoreNumberGeneratorRequest $request): RedirectResponse
   {
      $this->service->create($this->companyService->ensureForUser($request->user())->id, $request->validated());
      return back()->with('success', 'Generator nomor berhasil dibuat.');
   }
}
