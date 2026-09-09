<?php

namespace App\Http\Controllers;

use App\Http\Requests\Company\IndexCompanyOrganizationRequest;
use App\Repositories\GudangRepository;
use App\Services\CompanyService;
use Inertia\Inertia;
use Inertia\Response;

class CompanyWarehouseController extends Controller
{
    public function __construct(
        private CompanyService $service,
        private GudangRepository $gudangRepository,
    ) {}

    public function index(IndexCompanyOrganizationRequest $request): Response
    {
        $company = $this->service->ensureForUser($request->user());

        return Inertia::render('Company/Warehouses/Index', [
            'items' => $this->gudangRepository->paginate($company->id, $request->validated()),
            'filters' => $request->validated(),
        ]);
    }
}
