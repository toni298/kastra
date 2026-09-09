<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompanyLogoController extends Controller
{
    public function __construct(private CompanyService $service) {}

    public function index(Request $request): Response
    {
        $company = $this->service->ensureForUser($request->user());
        abort_unless($request->user()->can('company.view'), 403);

        return Inertia::render('Company/Logo/Index', $this->companyProps($company));
    }

    private function companyProps(Company $company): array
    {
        return [
            'company' => [
                ...$company->only(['id']),
                'logo_url' => $company->logo_path ? Storage::disk('public')->url($company->logo_path) : null,
            ],
        ];
    }
}
