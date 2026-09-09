<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\UpdateCompanyFeaturesRequest;
use App\Services\RbacService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CompanyFeaturesController extends Controller
{
    public function index(Request $request): Response
    {
        $company = $request->user()->company;

        return Inertia::render('Company/Features/Index', [
            'companyFeatures' => $company->features ?? [],
            'featureCatalog' => RbacService::featureCatalog(),
        ]);
    }

    public function update(UpdateCompanyFeaturesRequest $request): RedirectResponse
    {
        $company = $request->user()->company;
        $features = array_values($request->validated('features', []));

        DB::transaction(function () use ($company, $features): void {
            $company->update([
                'features' => $features,
                'organization_mode' => RbacService::organizationModeForFeatures($features),
            ]);

            // Re-sync permission semua role (owner, admin, karyawan) sesuai fitur baru
            app(RbacService::class)->syncCompanyFeatures($company);
        });

        return back()->with('success', 'Fitur perusahaan berhasil diperbarui.');
    }
}
