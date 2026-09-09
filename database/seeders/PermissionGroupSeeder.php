<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Services\RbacService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\PermissionRegistrar;

/**
 * Mengisi kolom group, subgroup, dan sort_order pada table permissions.
 * Ini menjadi single source of truth untuk struktur menu permission:
 * subgroup = Submenu (Produk, Kategori, Brand, Satuan, dll.)
 * sort_order = urutan tampil dalam group
 *
 * Permission baru yang belum terdaftar di sini akan diberi
 * group/subgroup null dan harus didaftarkan manual nantinya.
 */
class PermissionGroupSeeder extends Seeder
{
    /** @var array<int, array{group: string, subgroup: string, sort: int, permissions: list<string>}> */
    private array $structure = [
        [
            'group' => 'Produk',
            'subgroup' => 'Produk',
            'sort' => 1,
            'permissions' => ['products.view', 'products.create', 'products.edit', 'products.delete'],
        ],
        [
            'group' => 'Produk',
            'subgroup' => 'Kategori',
            'sort' => 2,
            'permissions' => ['product_categories.view', 'product_categories.create', 'product_categories.edit', 'product_categories.delete'],
        ],
        [
            'group' => 'Produk',
            'subgroup' => 'Brand',
            'sort' => 3,
            'permissions' => ['product_brands.view', 'product_brands.create', 'product_brands.edit', 'product_brands.delete'],
        ],
        [
            'group' => 'Produk',
            'subgroup' => 'Satuan',
            'sort' => 4,
            'permissions' => ['units.view', 'units.create', 'units.edit', 'units.delete'],
        ],
        [
            'group' => 'Pembelian',
            'subgroup' => 'Supplier',
            'sort' => 1,
            'permissions' => ['suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete'],
        ],
        [
            'group' => 'Pengguna',
            'subgroup' => 'Pengguna',
            'sort' => 1,
            'permissions' => ['users.view', 'users.create', 'users.edit', 'users.delete'],
        ],
        [
            'group' => 'Persediaan',
            'subgroup' => 'Ringkasan',
            'sort' => 1,
            'permissions' => ['inventory.summary.view', 'inventory.summary.discount', 'inventory.summary.adjust'],
        ],
        [
            'group' => 'Persediaan',
            'subgroup' => 'Stok Gudang',
            'sort' => 2,
            'permissions' => ['inventory.stock.view', 'inventory.stock.create', 'inventory.stock.edit', 'inventory.stock.delete'],
        ],
        [
            'group' => 'Persediaan',
            'subgroup' => 'Transfer',
            'sort' => 3,
            'permissions' => ['inventory.transfers.view', 'inventory.transfers.create', 'inventory.transfers.edit', 'inventory.transfers.delete', 'inventory.transfers.receive'],
        ],
        [
            'group' => 'Persediaan',
            'subgroup' => 'Stok Opname',
            'sort' => 4,
            'permissions' => ['inventory.opname.view', 'inventory.opname.create', 'inventory.opname.edit', 'inventory.opname.delete', 'inventory.opname.process'],
        ],
        [
            'group' => 'Penjualan',
            'subgroup' => 'Ringkasan',
            'sort' => 1,
            'permissions' => ['penjualan.summary.view'],
        ],
        [
            'group' => 'Penjualan',
            'subgroup' => 'Transaksi',
            'sort' => 2,
            'permissions' => ['penjualan.transactions.view', 'penjualan.transactions.create', 'penjualan.transactions.edit', 'penjualan.transactions.delete', 'penjualan.transactions.return', 'penjualan.transactions.print', 'penjualan.transactions.payment'],
        ],
        [
            'group' => 'Penjualan',
            'subgroup' => 'Retur',
            'sort' => 3,
            'permissions' => ['penjualan.returns.view'],
        ],
        [
            'group' => 'Penjualan',
            'subgroup' => 'Pelanggan',
            'sort' => 4,
            'permissions' => ['penjualan.customers.view', 'penjualan.customers.create', 'penjualan.customers.edit', 'penjualan.customers.delete'],
        ],
        [
            'group' => 'Kasir',
            'subgroup' => 'Akses',
            'sort' => 1,
            'permissions' => ['cashier.access'],
        ],
        [
            'group' => 'Pembelian',
            'subgroup' => 'Transaksi',
            'sort' => 1,
            'permissions' => ['pembelian.view', 'pembelian.create', 'pembelian.edit', 'pembelian.delete', 'pembelian.return', 'pembelian.print', 'pembelian.complete', 'pembelian.pay'],
        ],
        [
            'group' => 'Pembelian',
            'subgroup' => 'Retur',
            'sort' => 3,
            'permissions' => ['pembelian.returns.view'],
        ],
        [
            'group' => 'Kas & Bank',
            'subgroup' => 'Transaksi',
            'sort' => 1,
            'permissions' => [
                'cash_bank.transactions.view',
                'cash_bank.transactions.create',
                'cash_bank.transactions.edit',
                'cash_bank.transactions.delete',
            ],
        ],
        [
            'group' => 'Kas & Bank',
            'subgroup' => 'Rekening & Kas',
            'sort' => 2,
            'permissions' => [
                'cash_bank.accounts.view',
                'cash_bank.accounts.create',
                'cash_bank.accounts.edit',
                'cash_bank.accounts.delete',
                'cash_bank.accounts.deactivate',
            ],
        ],
        [
            'group' => 'Kas & Bank',
            'subgroup' => 'Transfer',
            'sort' => 3,
            'permissions' => ['cash_bank.transfers.view', 'cash_bank.transfers.create'],
        ],
        [
            'group' => 'Akuntansi',
            'subgroup' => 'Overview',
            'sort' => 1,
            'permissions' => ['accounting.view'],
        ],
        [
            'group' => 'Laporan & Analitik',
            'subgroup' => 'Penjualan',
            'sort' => 2,
            'permissions' => ['laporan.view', 'laporan.export'],
        ],
        [
            'group' => 'Akuntansi',
            'subgroup' => 'Pengaturan',
            'sort' => 3,
            'permissions' => ['coa.view'],
        ],
        [
            'group' => 'Profil Bisnis',
            'subgroup' => 'Informasi',
            'sort' => 1,
            'permissions' => ['company.view', 'company.edit', 'company.settings', 'company.logo'],
        ],
        [
            'group' => 'Profil Bisnis',
            'subgroup' => 'Pengaturan Pajak',
            'sort' => 2,
            'permissions' => ['taxes.view', 'taxes.create'],
        ],
        [
            'group' => 'Profil Bisnis',
            'subgroup' => 'Cabang',
            'sort' => 2,
            'permissions' => ['cabang.view', 'cabang.create', 'cabang.edit', 'cabang.delete'],
        ],
        [
            'group' => 'Profil Bisnis',
            'subgroup' => 'Outlet',
            'sort' => 3,
            'permissions' => ['outlet.view', 'outlet.create', 'outlet.edit', 'outlet.delete'],
        ],
        [
            'group' => 'Profil Bisnis',
            'subgroup' => 'Gudang',
            'sort' => 4,
            'permissions' => ['gudang.view', 'gudang.create', 'gudang.edit', 'gudang.delete'],
        ],
        [
            'group' => 'Role',
            'subgroup' => 'Role',
            'sort' => 1,
            'permissions' => ['roles.view', 'roles.create', 'roles.edit', 'roles.delete'],
        ],
        [
            'group' => 'Reminder',
            'subgroup' => 'Reminder',
            'sort' => 1,
            'permissions' => ['reminders.view', 'reminders.create', 'reminders.edit', 'reminders.delete'],
        ],
        [
            'group' => 'E-Commerce',
            'subgroup' => 'Pengaturan Toko',
            'sort' => 1,
            'permissions' => ['store.settings.view', 'store.settings.edit'],
        ],
        [
            'group' => 'E-Commerce',
            'subgroup' => 'Pesanan',
            'sort' => 2,
            'permissions' => ['store.orders.view', 'store.orders.update'],
        ],
    ];

    public function run(RbacService $rbac): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (['Akuntansi', 'Laporan & Analitik', 'Kas & Bank', 'Kasir', 'Pembelian', 'Supplier', 'Penjualan', 'Role', 'Pengaturan', 'Reminder', 'E-Commerce'] as $group) {
            $groupPermissions = array_merge(
                ...array_map(
                    static fn(array $entry): array => $entry['permissions'],
                    array_filter($this->structure, static fn(array $entry): bool => $entry['group'] === $group)
                )
            );

            Permission::query()
                ->where('guard_name', 'web')
                ->where('group', $group)
                ->whereNotIn('name', $groupPermissions)
                ->update([
                    'group' => null,
                    'subgroup' => null,
                    'sort_order' => 0,
                ]);
        }

        Permission::query()
            ->where('guard_name', 'web')
            ->whereNotIn('name', $rbac->permissionCatalog())
            ->update([
                'group' => null,
                'subgroup' => null,
                'sort_order' => 0,
            ]);

        foreach ($this->structure as $entry) {
            Permission::query()
                ->whereIn('name', $entry['permissions'])
                ->where('guard_name', 'web')
                ->update([
                    'group' => $entry['group'],
                    'subgroup' => $entry['subgroup'],
                    'sort_order' => $entry['sort'],
                ]);
        }

        Cache::forget('permission-options');

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
