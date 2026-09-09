<?php

namespace App\Http\Controllers;

use App\Http\Requests\Company\UpdateCompanyProfileRequest;
use App\Http\Requests\Company\UpdateCompanySettingsRequest;
use App\Http\Requests\Company\UploadCompanyLogoRequest;
use App\Models\Company;
use App\Services\CompanyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function __construct(private CompanyService $service) {}

    public function update(UpdateCompanyProfileRequest $request, Company $company): RedirectResponse
    {
        $this->service->updateProfile($company, $request->validated());

        return back()->with('success', 'Profil perusahaan berhasil diperbarui.');
    }

    public function updateSettings(UpdateCompanySettingsRequest $request, Company $company): RedirectResponse
    {
        $this->service->updateSettings($company, $request->validated());

        return back()->with('success', 'Pengaturan perusahaan berhasil diperbarui.');
    }

    public function uploadLogo(UploadCompanyLogoRequest $request, Company $company): RedirectResponse
    {
        $this->service->replaceLogo($company, $request->file('logo'));

        return back()->with('success', 'Logo perusahaan berhasil diperbarui.');
    }

    public function destroyLogo(Request $request, Company $company): RedirectResponse
    {
        abort_unless($request->user()->can('company.logo') && (string) $request->user()->company_id === (string) $company->getKey(), 403);
        $this->service->removeLogo($company);

        return back()->with('success', 'Logo perusahaan berhasil dihapus.');
    }
}
