<?php

namespace App\Http\Controllers\Company\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\Store\UpdateStoreAppearanceRequest;
use App\Http\Requests\Company\Store\UpdateStoreDomainRequest;
use App\Http\Requests\Company\Store\UpdateStorePaymentRequest;
use App\Http\Requests\Company\Store\UpdateStoreShippingRequest;
use App\Services\StoreSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreSettingsController extends Controller
{
    public function __construct(private StoreSettingsService $service) {}

    public function appearance(Request $request): Response
    {
        $company = $request->user()->company;
        $settings = $this->service->getForCompany($company);

        return Inertia::render('Ecommerce/Settings/Appearance', [
            'storeSettings' => $this->serialize($settings),
        ]);
    }

    public function updateAppearance(UpdateStoreAppearanceRequest $request): RedirectResponse
    {
        $this->service->updateAppearance($request->user()->company, $request->validated());

        return back()->with('success', 'Tampilan toko berhasil diperbarui.');
    }

    public function payment(Request $request): Response
    {
        $company = $request->user()->company;
        $settings = $this->service->getForCompany($company);

        return Inertia::render('Ecommerce/Settings/Payment', [
            'storeSettings' => $this->serialize($settings),
            'bankAccounts' => $company->storeBankAccounts()
                ->get(['id', 'bank_name', 'account_number', 'account_name', 'is_active', 'sort_order']),
        ]);
    }

    public function updatePayment(UpdateStorePaymentRequest $request): RedirectResponse
    {
        $this->service->updatePayment($request->user()->company, $request->validated());

        return back()->with('success', 'Pengaturan pembayaran berhasil diperbarui.');
    }

    public function shipping(Request $request): Response
    {
        $company = $request->user()->company;
        $settings = $this->service->getForCompany($company);

        return Inertia::render('Ecommerce/Settings/Shipping', [
            'storeSettings' => $this->serialize($settings),
        ]);
    }

    public function updateShipping(UpdateStoreShippingRequest $request): RedirectResponse
    {
        $this->service->updateShipping($request->user()->company, $request->validated());

        return back()->with('success', 'Pengaturan pengiriman berhasil diperbarui.');
    }

    public function domain(Request $request): Response
    {
        $company = $request->user()->company;
        $settings = $this->service->getForCompany($company);

        return Inertia::render('Ecommerce/Settings/Domain', [
            'storeSettings' => $this->serialize($settings),
            'baseDomain' => config('app.store_base_domain', parse_url(config('app.url'), PHP_URL_HOST)),
        ]);
    }

    public function updateDomain(UpdateStoreDomainRequest $request): RedirectResponse
    {
        $this->service->updateDomain($request->user()->company, $request->validated());

        return back()->with('success', 'Pengaturan domain berhasil disimpan.');
    }

    /**
     * Halaman editor modern (live preview + section manager).
     */
    public function editor(Request $request): Response
    {
        $company = $request->user()->company;
        $settings = $this->service->getForCompany($company);

        $branch = $company->branches()->where('is_store_enabled', true)->first()
            ?? $company->branches()->first();

        return Inertia::render('Ecommerce/Settings/Editor', [
            'storeSettings' => $this->serialize($settings),
            'sections' => $settings->sections,
            'sectionTypes' => \App\Models\StoreSetting::sectionTypes(),
            'bankAccounts' => $company->storeBankAccounts()
                ->get(['id', 'bank_name', 'account_number', 'account_name', 'is_active', 'sort_order']),
            'baseDomain' => config('app.store_base_domain', parse_url(config('app.url'), PHP_URL_HOST)),
            'companyName' => $company->name,
            'companySlug' => $company->slug,
            'branchName' => $branch?->name ?? $company->name,
            'branchSlug' => $branch?->slug,
            'storefrontUrl' => ($company->slug && $branch?->slug)
                ? route('store.index', ['company' => $company->slug, 'branch' => $branch->slug])
                : null,
        ]);
    }

    /**
     * Update sections (tambah/hapus/reorder/toggle).
     */
    public function updateSections(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sections' => ['required', 'array'],
            'sections.*.key' => ['nullable', 'string', 'max:64'],
            'sections.*.id' => ['nullable', 'string', 'max:64'],
            'sections.*.type' => ['nullable', 'string', 'max:40'],
            'sections.*.label' => ['required', 'string', 'max:120'],
            'sections.*.enabled' => ['boolean'],
            'sections.*.config' => ['nullable', 'array'],
        ]);

        $this->service->updateSections($request->user()->company, $data['sections']);

        return back()->with('success', 'Section berhasil diperbarui.');
    }

    protected function serialize(\App\Models\StoreSetting $settings): array
    {
        return array_merge($settings->toArray(), [
            'banner_url' => $settings->banner_url,
            'logo_url' => $settings->logo_url,
            'qris_image_url' => $settings->qris_image_url,
        ]);
    }
}
