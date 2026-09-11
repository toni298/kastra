<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Company;
use App\Models\User;
use App\Services\InertiaAuthorizationService;
use Spatie\Permission\PermissionRegistrar;

class RbacService
{
    /**
     * Permission inti yang SELALU diberikan terlepas dari fitur yang dipilih.
     * Tanpa ini user tidak bisa login, melihat dashboard, atau mengelola perusahaan/tim.
     */
    public const CORE_PERMISSIONS = [
        'company.view',
        'company.edit',
        'company.settings',
        'company.logo',
        'users.view',
        'users.create',
        'users.edit',
        'users.delete',
        'roles.view',
        'roles.create',
        'roles.edit',
        'roles.delete',
        'cabang.view',
        'cabang.create',
        'cabang.edit',
        'cabang.delete',
        'outlet.view',
        'outlet.create',
        'outlet.edit',
        'outlet.delete',
        'taxes.view',
        'taxes.create',
        'taxes.edit',
        'taxes.delete',
        'number_generators.view',
        'number_generators.create',
        'number_generators.edit',
        'number_generators.delete',
    ];

    public const OWNER_ONLY_PERMISSIONS = [
        'inventory.reconcile',
    ];

    /**
     * Role bawaan yang selalu dibuat untuk setiap company.
     */
    public const DEFAULT_ROLES = ['owner', 'admin', 'karyawan', 'kasir'];

    /**
     * Permission standar role Kasir (POS) — subset dari fitur 'pos'
     * yang dibutuhkan agar halaman kasir bisa berfungsi penuh:
     * akses layar kasir, katalog produk, pelanggan, transaksi,
     * pembayaran (termasuk akun kas/bank), dan cetak struk.
     */
    public const KASIR_PERMISSIONS = [
        // Akses utama ke halaman kasir & dashboard kasir
        'cashier.access',
        'penjualan.summary.view',
        // Katalog produk untuk pencarian & keranjang
        'products.view',
        'product_categories.view',
        // Pelanggan
        'penjualan.customers.view',
        'penjualan.customers.create',
        // Transaksi POS (buat, lihat riwayat, proses pembayaran, cetak struk)
        'penjualan.transactions.view',
        'penjualan.transactions.create',
        'penjualan.transactions.payment',
        'penjualan.transactions.print',
        'penjualan.returns.view',
        // Akun kas/bank tujuan pembayaran saat checkout
        'cash_bank.accounts.view',
    ];

    /**
     * Mapping fitur onboarding → daftar permission (menggunakan nama permission asli di catalog).
     * Setiap fitur mencakup modul utama + dependency minimal agar fitur bisa berfungsi.
     */
    public const FEATURE_PERMISSIONS = [
        'pos' => [
            'cashier.access',
            'penjualan.summary.view',
            'penjualan.transactions.view',
            'penjualan.transactions.create',
            'penjualan.transactions.edit',
            'penjualan.transactions.return',
            'penjualan.transactions.print',
            'penjualan.transactions.payment',
            'penjualan.returns.view',
            // Dependency: master produk
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'product_categories.view',
            'product_categories.create',
            'product_categories.edit',
            'product_categories.delete',
            'product_brands.view',
            'product_brands.create',
            'product_brands.edit',
            'product_brands.delete',
            'units.view',
            'units.create',
            'units.edit',
            'units.delete',
            // Dependency: pelanggan
            'penjualan.customers.view',
            'penjualan.customers.create',
            'penjualan.customers.edit',
            'penjualan.customers.delete',
            // Dependency: kas (uang hasil POS masuk ke kas)
            'cash_bank.view',
            'cash_bank.accounts.view',
            'cash_bank.transactions.view',
            // Dependency: penyesuaian stok dari POS, sama seperti E-Commerce
            'inventory.summary.view',
            'inventory.summary.discount',
            'inventory.summary.adjust',
            'inventory.movements.view',
        ],
        'inventory' => [
            'gudang.view',
            'gudang.create',
            'gudang.edit',
            'gudang.delete',
            'inventory.stock.view',
            'inventory.stock.create',
            'inventory.reconcile',
            'inventory.movements.view',
            'inventory.stock.edit',
            'inventory.stock.delete',
            'inventory.transfers.view',
            'inventory.transfers.create',
            'inventory.transfers.edit',
            'inventory.transfers.delete',
            'inventory.transfers.receive',
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'product_categories.view',
            'product_categories.create',
            'product_categories.edit',
            'product_categories.delete',
            'product_brands.view',
            'product_brands.create',
            'product_brands.edit',
            'product_brands.delete',
            'units.view',
            'units.create',
            'units.edit',
            'units.delete',
            'bahan.view',
            'bahan.create',
            'bahan.edit',
            'bahan.delete',
            'inventory.summary.view',
            'inventory.summary.discount',
            'inventory.summary.adjust',
            'inventory.opname.view',
            'inventory.opname.create',
            'inventory.opname.edit',
            'inventory.opname.delete',
            'inventory.opname.process',
        ],
        'purchase' => [
            'pembelian.view',
            'pembelian.create',
            'pembelian.edit',
            'pembelian.delete',
            'pembelian.return',
            'pembelian.print',
            'pembelian.complete',
            'pembelian.pay',
            'pembelian.returns.view',
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            // Dependency: master produk
            'products.view',
            'products.create',
            'products.edit',
            'product_categories.view',
            'units.view',
            'inventory.stock.view',
            'inventory.movements.view',
            'cash_bank.accounts.view',
        ],
        'sales' => [
            'penjualan.summary.view',
            'penjualan.transactions.view',
            'penjualan.transactions.create',
            'penjualan.transactions.edit',
            'penjualan.transactions.delete',
            'penjualan.transactions.return',
            'penjualan.transactions.print',
            'penjualan.transactions.payment',
            'penjualan.returns.view',
            'penjualan.customers.view',
            'penjualan.customers.create',
            'penjualan.customers.edit',
            'penjualan.customers.delete',
            // Dependency: master produk
            'products.view',
            'product_categories.view',
            'units.view',
            'inventory.movements.view',
        ],
        'warehouse' => [
            'gudang.view',
            'gudang.create',
            'gudang.edit',
            'gudang.delete',
            'inventory.stock.view',
            'inventory.stock.create',
            'inventory.reconcile',
            'inventory.movements.view',
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'product_categories.view',
            'product_categories.create',
            'product_categories.edit',
            'product_categories.delete',
            'product_brands.view',
            'product_brands.create',
            'product_brands.edit',
            'product_brands.delete',
            'units.view',
            'units.create',
            'units.edit',
            'units.delete',
            'inventory.transfers.view',
            'inventory.transfers.create',
            'inventory.transfers.edit',
            'inventory.transfers.delete',
            'inventory.transfers.receive',
            'inventory.opname.view',
            'inventory.opname.create',
            'inventory.opname.edit',
            'inventory.opname.delete',
            'inventory.opname.process',
        ],
        'finance' => [
            'cash_bank.view',
            'cash_bank.accounts.view',
            'cash_bank.accounts.create',
            'cash_bank.accounts.edit',
            'cash_bank.accounts.delete',
            'cash_bank.accounts.deactivate',
            'cash_bank.transactions.view',
            'cash_bank.transactions.create',
            'cash_bank.transactions.edit',
            'cash_bank.transactions.delete',
            'cash_bank.transfers.view',
            'cash_bank.transfers.create',
            'cash_bank.settings.view',
            'cash_bank.settings.create',
            'cash_bank.settings.edit',
            'cash_bank.settings.delete',
            'accounting.view',
            'coa.view',
            'coa.create',
            'coa.edit',
            'coa.delete',
        ],
        'hr' => [
            'hr.employees.view',
            'hr.employees.create',
            'hr.employees.edit',
            'hr.employees.delete',
        ],
        'report' => [
            'laporan.view',
            'laporan.export',
        ],
        'ecommerce' => [
            'store.settings.view',
            'store.settings.edit',
            'store.orders.view',
            'store.orders.update',
            // Dependency: manajemen produk untuk katalog toko online
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'product_categories.view',
            'product_categories.create',
            'product_categories.edit',
            'product_categories.delete',
            'product_brands.view',
            'product_brands.create',
            'product_brands.edit',
            'product_brands.delete',
            'units.view',
            'units.create',
            'units.edit',
            'units.delete',
            // Dependency: manajemen penjualan dan pelanggan
            'penjualan.summary.view',
            'penjualan.transactions.view',
            'penjualan.transactions.create',
            'penjualan.transactions.edit',
            'penjualan.transactions.delete',
            'penjualan.transactions.return',
            'penjualan.transactions.print',
            'penjualan.transactions.payment',
            'penjualan.returns.view',
            'penjualan.customers.view',
            'penjualan.customers.create',
            'penjualan.customers.edit',
            'penjualan.customers.delete',
            'inventory.summary.view',
            'inventory.summary.discount',
            'inventory.summary.adjust',
            'inventory.movements.view',
        ],
        'crm' => [
            // Belum ada permission CRM di catalog — placeholder untuk pengembangan selanjutnya
        ],
    ];

    /**
     * Daftar fitur valid yang bisa dipilih saat onboarding.
     */
    public static function availableFeatures(): array
    {
        return array_keys(self::FEATURE_PERMISSIONS);
    }

    public static function organizationModeForFeatures(?array $features): string
    {
        $features = array_values($features ?? []);
        $requiresBranches = (bool) array_intersect(['pos', 'sales', 'ecommerce'], $features);
        $requiresWarehouses = (bool) array_intersect(['inventory', 'purchase', 'warehouse'], $features);

        return match (true) {
            $requiresBranches && $requiresWarehouses => 'branch_warehouse',
            $requiresWarehouses => 'warehouse',
            default => 'branch',
        };
    }

    /**
     * Metadata fitur untuk ditampilkan di UI (onboarding & pengaturan).
     * 'enabled' = false berarti modul belum tersedia (belum ada permission di catalog).
     */
    public static function featureCatalog(): array
    {
        return [
            ['id' => 'pos', 'name' => 'Point of Sale (POS)', 'description' => 'Kasir, keranjang belanja, dan pembayaran.', 'icon' => 'ShoppingCart', 'enabled' => true],
            ['id' => 'inventory', 'name' => 'Manajemen Stok', 'description' => 'Kelola stok produk, opname, dan stok awal.', 'icon' => 'Package', 'enabled' => true],
            ['id' => 'purchase', 'name' => 'Pembelian', 'description' => 'Purchase order, penerimaan barang, dan supplier.', 'icon' => 'ShoppingBag', 'enabled' => true],
            ['id' => 'sales', 'name' => 'Penjualan', 'description' => 'Invoice, transaksi penjualan, dan pelanggan.', 'icon' => 'Receipt', 'enabled' => true],
            ['id' => 'warehouse', 'name' => 'Gudang', 'description' => 'Multi gudang, transfer antar gudang, dan tracking stok.', 'icon' => 'Warehouse', 'enabled' => true],
            ['id' => 'finance', 'name' => 'Keuangan', 'description' => 'Kas, bank, transfer dana, dan akuntansi.', 'icon' => 'Landmark', 'enabled' => true],
            ['id' => 'report', 'name' => 'Laporan & Analitik', 'description' => 'Laporan penjualan, pembelian, stok, dan keuangan.', 'icon' => 'ChartNoAxesColumn', 'enabled' => true],
            ['id' => 'hr', 'name' => 'SDM / HR', 'description' => 'Karyawan, absensi, dan penggajian.', 'icon' => 'Users', 'enabled' => true],
            ['id' => 'ecommerce', 'name' => 'E-Commerce', 'description' => 'Toko online, katalog produk, dan pesanan online.', 'icon' => 'Globe', 'enabled' => true],
            ['id' => 'crm', 'name' => 'CRM', 'description' => 'Manajemen pelanggan dan follow up.', 'icon' => 'Handshake', 'enabled' => false],
        ];
    }

    /**
     * Re-sync permission semua role company berdasarkan fitur yang tersimpan.
     * Dipakai saat fitur diubah dari halaman pengaturan.
     */
    public function syncCompanyFeatures(Company $company): void
    {
        $this->bootstrapCompany($company, $company->features);

        // Sinkronkan flag storefront branch mengikuti fitur ecommerce.
        $this->syncStoreEnabledBranches($company);

        // Invalidate snapshot permission Inertia seluruh user company agar
        // menu/sidebar langsung berubah pada request berikutnya (realtime),
        // tanpa menunggu cache 5 menit kedaluwarsa.
        app(InertiaAuthorizationService::class)->forgetMany(
            $company->users()->get(['id'])
        );
    }

    /**
     * Aktifkan is_store_enabled pada branch default saat fitur ecommerce aktif,
     * dan matikan seluruhnya saat fitur dinonaktifkan. Tanpa ini, mengaktifkan
     * fitur ecommerce tidak otomatis membuka storefront (ResolveStore -> 404).
     */
    public function syncStoreEnabledBranches(Company $company): void
    {
        $enabled = in_array('ecommerce', $company->features ?? [], true);

        if ($enabled) {
            // Jika belum ada satupun branch store-enabled, aktifkan yang default
            // (fallback: branch aktif pertama) agar storefront langsung bisa diakses.
            $hasEnabled = $company->branches()->where('is_store_enabled', true)->exists();

            if (! $hasEnabled) {
                $branch = $company->branches()->where('is_default', true)->first()
                    ?? $company->branches()->where('status', \App\Models\Branch::STATUS_ACTIVE)->first();

                $branch?->update(['is_store_enabled' => true]);
            }
        } else {
            // Fitur ecommerce dimatikan -> tutup seluruh storefront.
            $company->branches()->where('is_store_enabled', true)
                ->update(['is_store_enabled' => false]);
        }
    }

    /**
     * Gabungkan CORE_PERMISSIONS + permission dari fitur yang dipilih.
     * Fitur null mempertahankan fallback legacy, sedangkan array kosong berarti
     * semua fitur opsional dimatikan dan hanya permission inti yang aktif.
     */
    public function permissionsForFeatures(?array $features): array
    {
        if ($features === null) {
            return $this->permissionCatalog();
        }

        $features = array_values(array_filter($features, fn($f) => isset(self::FEATURE_PERMISSIONS[$f])));
        $names = collect(self::CORE_PERMISSIONS);
        foreach ($features as $feature) {
            $names = $names->merge(self::FEATURE_PERMISSIONS[$feature]);
        }

        $catalog = collect($this->permissionCatalog());

        return $names->unique()
            ->filter(fn($name) => $catalog->contains($name))
            ->values()
            ->all();
    }

    /**
     * Permission read-only untuk role karyawan, difilter sesuai fitur terpilih.
     * Fitur null mempertahankan fallback legacy, sedangkan array kosong hanya
     * menyisakan permission operasional inti yang memang ada di role karyawan.
     */
    public function karyawanPermissionsForFeatures(?array $features): array
    {
        if ($features === null) {
            return $this->karyawanPermissions();
        }

        $features = array_values(array_filter($features, fn($f) => isset(self::FEATURE_PERMISSIONS[$f])));
        $allowed = collect($this->permissionsForFeatures($features));

        return collect($this->karyawanPermissions())
            ->filter(fn($name) => $allowed->contains($name))
            ->values()
            ->all();
    }

    /**
     * Permission standar kasir, difilter sesuai catalog.
     */
    public function kasirPermissions(): array
    {
        $catalog = collect($this->permissionCatalog());

        return collect(self::KASIR_PERMISSIONS)
            ->filter(fn($name) => $catalog->contains($name))
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public function permissionCatalog(): array
    {
        return [
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'hr.employees.view',
            'hr.employees.create',
            'hr.employees.edit',
            'hr.employees.delete',
            'company.view',
            'company.edit',
            'company.settings',
            'company.logo',
            'roles.view',
            'roles.create',
            'roles.edit',
            'roles.delete',
            'permissions.view',
            'permissions.create',
            'permissions.edit',
            'permissions.delete',
            'cabang.view',
            'cabang.create',
            'cabang.edit',
            'cabang.delete',
            'outlet.view',
            'outlet.create',
            'outlet.edit',
            'outlet.delete',
            'gudang.view',
            'gudang.create',
            'gudang.edit',
            'gudang.delete',
            'bahan.view',
            'bahan.create',
            'bahan.edit',
            'bahan.delete',
            'inventory.summary.view',
            'inventory.summary.discount',
            'inventory.summary.adjust',
            'inventory.reconcile',
            'inventory.movements.view',
            'inventory.stock.view',
            'inventory.stock.create',
            'inventory.stock.edit',
            'inventory.stock.delete',
            'inventory.transfers.view',
            'inventory.transfers.create',
            'inventory.transfers.edit',
            'inventory.transfers.delete',
            'inventory.transfers.receive',
            'inventory.opname.view',
            'inventory.opname.create',
            'inventory.opname.edit',
            'inventory.opname.delete',
            'inventory.opname.process',
            'pembelian.view',
            'pembelian.create',
            'pembelian.edit',
            'pembelian.delete',
            'pembelian.return',
            'pembelian.print',
            'pembelian.complete',
            'pembelian.pay',
            'pembelian.returns.view',
            'penjualan.summary.view',
            'penjualan.transactions.view',
            'penjualan.transactions.create',
            'penjualan.transactions.edit',
            'penjualan.transactions.delete',
            'penjualan.transactions.return',
            'penjualan.transactions.print',
            'penjualan.transactions.payment',
            'penjualan.returns.view',
            'penjualan.customers.view',
            'penjualan.customers.create',
            'penjualan.customers.edit',
            'penjualan.customers.delete',
            'cashier.access',
            'laporan.view',
            'laporan.export',
            'store.settings.view',
            'store.settings.edit',
            'store.orders.view',
            'store.orders.update',
            'accounting.view',
            'coa.view',
            'coa.create',
            'coa.edit',
            'coa.delete',
            'taxes.view',
            'taxes.create',
            'taxes.edit',
            'taxes.delete',
            'number_generators.view',
            'number_generators.create',
            'number_generators.edit',
            'number_generators.delete',
            'products.view',
            'products.create',
            'products.edit',
            'products.delete',
            'product_categories.view',
            'product_categories.create',
            'product_categories.edit',
            'product_categories.delete',
            'product_brands.view',
            'product_brands.create',
            'product_brands.edit',
            'product_brands.delete',
            'units.view',
            'units.create',
            'units.edit',
            'units.delete',
            'suppliers.view',
            'suppliers.create',
            'suppliers.edit',
            'suppliers.delete',
            'cash_bank.view',
            'cash_bank.transactions.view',
            'cash_bank.transactions.create',
            'cash_bank.transactions.edit',
            'cash_bank.transactions.delete',
            'cash_bank.accounts.view',
            'cash_bank.accounts.create',
            'cash_bank.accounts.edit',
            'cash_bank.accounts.delete',
            'cash_bank.accounts.deactivate',
            'cash_bank.transfers.view',
            'cash_bank.transfers.create',
            'cash_bank.settings.view',
            'cash_bank.settings.create',
            'cash_bank.settings.edit',
            'cash_bank.settings.delete',
            'reminders.view',
            'reminders.create',
            'reminders.edit',
            'reminders.delete',
        ];
    }

    public function bootstrap(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($this->permissionCatalog() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::DEFAULT_ROLES as $role) {
            Role::query()->firstOrCreate([
                'company_id' => null,
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }

        $this->syncRolePermissions(null);

        Company::query()->orderBy('id')->chunk(100, function ($companies): void {
            foreach ($companies as $company) {
                $this->syncRolePermissions($company->id);
                $this->syncCompanyOwners($company);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function bootstrapCompany(Company $company, ?array $features = null): void
    {
        foreach ($this->permissionCatalog() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        foreach (self::DEFAULT_ROLES as $name) {
            Role::query()->firstOrCreate([
                'company_id' => $company->id,
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $this->syncRolePermissions($company->id, $features);
        $this->syncCompanyOwners($company);
        $this->ensureOwnerReminderPermissions($company);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function ensureOwnerReminderPermissions(Company $company): void
    {
        $ownerRole = Role::query()
            ->where('company_id', $company->id)
            ->where('name', 'owner')
            ->where('guard_name', 'web')
            ->first();

        $ownerRole?->givePermissionTo([
            'reminders.view',
            'reminders.create',
            'reminders.edit',
            'reminders.delete',
        ]);
    }

    private function syncRolePermissions(?string $companyId, ?array $features = null): void
    {
        if ($features !== null) {
            $ownerPerms = $this->permissionsForFeatures($features);
            $karyawanPerms = $this->karyawanPermissionsForFeatures($features);
        } else {
            $ownerPerms = $this->permissionCatalog();
            $karyawanPerms = $this->karyawanPermissions();
        }

        $this->syncRole($companyId, 'owner', $ownerPerms);
        $adminPerms = array_values(array_diff($ownerPerms, self::OWNER_ONLY_PERMISSIONS));
        $this->syncRole($companyId, 'admin', $adminPerms);
        $this->syncRole($companyId, 'karyawan', $karyawanPerms);
        $this->syncRole($companyId, 'kasir', $this->kasirPermissionsForFeatures($features));
    }

    /**
     * Role kasir hanya aktif (memiliki permission) saat fitur POS dipilih.
     * Saat fitur dinonaktifkan atau daftar fitur tidak diketahui → permission dikosongkan.
     */
    private function kasirPermissionsForFeatures(?array $features): array
    {
        if ($features === null || ! in_array('pos', $features, true)) {
            return [];
        }

        $permissions = $this->kasirPermissions();

        if (in_array('report', $features, true)) {
            $permissions[] = 'laporan.view';
        }

        return array_values(array_unique($permissions));
    }

    /**
     * @param  list<string>  $permissions
     */
    private function syncRole(?string $companyId, string $name, array $permissions): void
    {
        $role = Role::query()
            ->where('company_id', $companyId)
            ->where('name', $name)
            ->where('guard_name', 'web')
            ->first();

        if ($role === null) {
            return;
        }

        $role->syncPermissions($permissions);
    }

    private function syncCompanyOwners(Company $company): void
    {
        $ownerRole = Role::query()
            ->where('company_id', $company->id)
            ->where('name', 'owner')
            ->where('guard_name', 'web')
            ->first();

        if ($ownerRole === null) {
            return;
        }

        User::query()
            ->where('company_id', $company->id)
            ->whereHas('roles', fn($query) => $query
                ->where('company_id', null)
                ->where('name', 'owner')
                ->where('guard_name', 'web'))
            ->each(fn(User $user) => $user->syncRoles([$ownerRole]));
    }

    /**
     * @return list<string>
     */
    private function adminPermissions(): array
    {
        return $this->permissionCatalog();
    }

    /**
     * @return list<string>
     */
    private function karyawanPermissions(): array
    {
        return [
            'products.view',
            'product_categories.view',
            'product_brands.view',
            'units.view',
            'suppliers.view',
            'inventory.summary.view',
            'inventory.stock.view',
            'inventory.movements.view',
            'inventory.transfers.view',
            'inventory.opname.view',
            'penjualan.summary.view',
            'penjualan.transactions.view',
            'penjualan.transactions.create',
            'penjualan.returns.view',
            'penjualan.customers.view',
            'pembelian.view',
            'cabang.view',
            'outlet.view',
            'gudang.view',
            'laporan.view',
            'cash_bank.view',
            'cash_bank.transactions.view',
            'cash_bank.transactions.create',
            'cash_bank.accounts.view',
            'cash_bank.transfers.view',
            'store.settings.view',
            'store.orders.view',
        ];
    }
}
