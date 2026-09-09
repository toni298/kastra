<?php

namespace App\Http\Controllers;

use App\Http\Requests\Company\IndexCompanyOrganizationRequest;
use App\Repositories\CabangRepository;
use App\Services\CompanyService;
use Inertia\Inertia;
use Inertia\Response;

class CompanyBranchController extends Controller
{
    public function __construct(
        private CompanyService $service,
        private CabangRepository $cabangRepository,
    ) {}

    public function index(IndexCompanyOrganizationRequest $request): Response
    {
        $company = $this->service->ensureForUser($request->user());

        return Inertia::render('Company/Branches/Index', [
            'items' => $this->cabangRepository->paginate($company->id, $request->validated()),
            'filters' => $request->validated(),
        ]);
    }
}
