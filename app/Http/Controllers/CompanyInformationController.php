<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CompanyInformationController extends Controller
{
    public function __construct(private CompanyService $service) {}

    public function index(Request $request): Response
    {
        $company = $this->service->ensureForUser($request->user());
        abort_unless($request->user()->can('company.view') && (string) $request->user()->company_id === (string) $company->getKey(), 403);

        return Inertia::render('Company/Index', $this->companyProps($company));
    }

    private function companyProps(Company $company): array
    {
        return [
            'company' => [
                ...$company->only([
                    'id', 'name', 'legal_name', 'npwp', 'nib', 'email', 'phone', 'address',
                    'city', 'province', 'postal_code', 'country_code', 'currency_code', 'timezone', 'locale',
                ]),
                'logo_url' => $company->logo_path ? Storage::disk('public')->url($company->logo_path) : null,
            ],
            'timezones' => collect(timezone_identifiers_list())
                ->filter(fn ($timezone) => str_starts_with($timezone, 'Asia/'))
                ->values(),
            'currencies' => ['IDR', 'USD', 'SGD', 'MYR', 'EUR'],
        ];
    }
}
