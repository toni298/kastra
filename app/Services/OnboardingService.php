<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\CashBankAccount;
use App\Models\CashBankAccountSetting;
use App\Models\Company;
use App\Models\Gudang;
use App\Models\TaxConfiguration;
use App\Models\User;
use App\Models\Role;
use App\Services\InertiaAuthorizationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnboardingService
{
    public function __construct(private RbacService $rbac) {}

    public function complete(User $user, array $data): void
    {
        DB::transaction(function () use ($user, $data) {
            $business = $data['business'];
            $features = $data['features'] ?? null;
            $organizationMode = RbacService::organizationModeForFeatures($features);
            $company = Company::create([
                'name' => $business['name'],
                'slug' => $this->generateUniqueSlug($business['name'], Company::class),
                'legal_name' => $business['legal_name'] ?? ($data['business_type'] === 'perusahaan' ? $business['name'] : null),
                'npwp' => $data['tax']['npwp'] ?? null,
                'phone' => $business['phone'],
                'address' => $business['address'],
                'city' => $business['city'],
                'province' => $business['province'] ?? null,
                'postal_code' => $business['postal_code'] ?? null,
                'organization_mode' => $organizationMode,
                'features' => $features,
            ]);

            if (isset($business['logo'])) {
                $filename = Str::uuid() . '.' . $business['logo']->extension();
                $logoPath = $business['logo']->storeAs("companies/{$company->id}/logo", $filename, 'public');
                $company->update(['logo_path' => $logoPath]);
            }

            if ($data['has_branches']) {
                foreach ($data['branches'] as $branch) {
                    Branch::create([
                        'company_id' => $company->id,
                        'code' => $branch['code'],
                        'name' => $branch['name'],
                        'slug' => $this->generateUniqueSlug($branch['name'], Branch::class),
                        'address' => $branch['address'],
                        'status' => Branch::STATUS_ACTIVE,
                        'is_default' => $branch === reset($data['branches']),
                    ]);
                }
            } else {
                Branch::create([
                    'company_id' => $company->id,
                    'code' => null,
                    'name' => $business['name'],
                    'slug' => $this->generateUniqueSlug($business['name'], Branch::class),
                    'phone' => $business['phone'],
                    'address' => $business['address'],
                    'city' => $business['city'],
                    'province' => $business['province'] ?? null,
                    'postal_code' => $business['postal_code'] ?? null,
                    'status' => Branch::STATUS_ACTIVE,
                    'is_default' => true,
                ]);
            }

            if ($data['has_warehouses']) {
                foreach ($data['warehouses'] as $warehouse) {
                    Gudang::create([
                        'company_id' => $company->id,
                        'kode' => $warehouse['code'],
                        'nama' => $warehouse['name'],
                        'alamat' => $warehouse['address'],
                        'aktif' => true,
                    ]);
                }
            } else {
                Gudang::create([
                    'company_id' => $company->id,
                    'kode' => 'GDG-01',
                    'nama' => 'Gudang ' . $business['name'],
                    'telepon' => $business['phone'],
                    'alamat' => $business['address'],
                    'kota' => $business['city'],
                    'provinsi' => $business['province'] ?? null,
                    'kode_pos' => $business['postal_code'] ?? null,
                    'aktif' => true,
                ]);
            }

            if ($data['tax']['enabled']) {
                TaxConfiguration::create([
                    'company_id' => $company->id,
                    'kode' => 'PPN',
                    'nama' => 'PPN (Pajak Pertambahan Nilai)',
                    'jenis' => 'penjualan',
                    'persentase' => $data['tax']['rate'],
                    'mode' => $data['tax']['mode'],
                    'aktif' => true,
                ]);
            }

            $cashBankAccount = CashBankAccount::create([
                'company_id' => $company->id,
                'name' => 'Kas Utama',
                'type' => 'cash',
                'account_number' => null,
                'account_holder' => $business['name'],
                'currency' => 'IDR',
                'opening_balance' => 0,
                'current_balance' => 0,
                'is_active' => true,
            ]);

            $company->branches()->each(function (Branch $branch) use ($company, $cashBankAccount): void {
                CashBankAccountSetting::create([
                    'company_id' => $company->id,
                    'cash_bank_account_id' => $cashBankAccount->id,
                    'branch_id' => $branch->id,
                    'can_receive_money' => true,
                    'can_send_money' => true,
                    'is_default_receive' => true,
                    'is_default_payment' => true,
                    'is_active' => true,
                ]);
            });

            $this->rbac->bootstrapCompany($company, $features);
            $ownerRole = Role::query()
                ->where('company_id', $company->id)
                ->where('name', 'owner')
                ->where('guard_name', 'web')
                ->firstOrFail();

            // Sinkronkan flag storefront branch mengikuti fitur yang dipilih.
            $this->rbac->syncStoreEnabledBranches($company);

            $user->update([
                'company_id' => $company->id,
                'onboarding_completed_at' => now(),
            ]);
            $user->syncRoles([$ownerRole]);

            // Invalidate snapshot permission Inertia agar menu/sidebar langsung
            // mencerminkan fitur yang dipilih saat onboarding (tanpa menunggu
            // cache 5 menit kedaluwarsa).
            app(InertiaAuthorizationService::class)->forget($user);
        });
    }

    private function generateUniqueSlug(string $name, string $modelClass): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while ($modelClass::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
