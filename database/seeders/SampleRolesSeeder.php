<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Role;
use Illuminate\Database\Seeder;

/**
 * SampleRolesSeeder - Create sample roles untuk setiap company
 *
 * Untuk testing & development purposes.
 * Run: php artisan db:seed --class=SampleRolesSeeder
 */
class SampleRolesSeeder extends Seeder
{
   /**
    * Default role templates dengan permission yang di-recommend
    */
   private array $roleTemplates = [
      'cashier' => [
         'display_name' => 'Kasir',
         'description' => 'Role untuk staff kasir/checkout',
         'permissions' => [
            'products.view',
            'product_categories.view',
            'product_brands.view',
            'units.view',
            'suppliers.view',
            'inventory.stock.view',
            'penjualan.summary.view',
            'penjualan.transactions.view',
            'penjualan.transactions.create',
            'penjualan.transactions.edit',
            'cabang.view',
            'outlet.view',
            'laporan.view',
         ],
      ],
      'warehouse_manager' => [
         'display_name' => 'Manager Gudang',
         'description' => 'Role untuk manager gudang/inventory',
         'permissions' => [
            'products.view',
            'product_categories.view',
            'product_brands.view',
            'units.view',
            'suppliers.view',
            'inventory.stock.view',
            'inventory.stock.create',
            'inventory.opname.view',
            'inventory.transfers.view',
            'pembelian.view',
            'penjualan.summary.view',
            'penjualan.transactions.view',
            'cabang.view',
            'outlet.view',
            'gudang.view',
            'laporan.view',
            'laporan.export',
         ],
      ],
      'purchasing_officer' => [
         'display_name' => 'Officer Pembelian',
         'description' => 'Role untuk staff pembelian/procurement',
         'permissions' => [
            'products.view',
            'product_categories.view',
            'suppliers.view',
            'pembelian.view',
            'pembelian.create',
            'pembelian.edit',
            'cabang.view',
            'outlet.view',
            'laporan.view',
         ],
      ],
      'accountant' => [
         'display_name' => 'Akuntan',
         'description' => 'Role untuk akuntan/finance staff',
         'permissions' => [
            'coa.view',
            'coa.create',
            'coa.edit',
            'coa.delete',
            'pembelian.view',
            'penjualan.summary.view',
            'penjualan.transactions.view',
            'laporan.view',
            'laporan.export',
            'cabang.view',
            'outlet.view',
         ],
      ],
   ];

   public function run(): void
   {
      // Get all companies
      $companies = Company::all();

      if ($companies->isEmpty()) {
         $this->command->warn('No companies found. Skipping sample roles creation.');
         return;
      }

      foreach ($companies as $company) {
         $this->createSampleRoles($company);
      }

      $this->command->info('Sample roles created successfully for all companies.');
   }

   private function createSampleRoles(Company $company): void
   {
      $companyId = $company->id;

      // Check if sample roles sudah ada
      $existingRoles = Role::where('company_id', $companyId)
         ->whereIn('name', array_keys($this->roleTemplates))
         ->count();

      if ($existingRoles > 0) {
         $this->command->info("Sample roles for {$company->name} already exist. Skipping...");
         return;
      }

      foreach ($this->roleTemplates as $roleName => $template) {
         $role = Role::create([
            'company_id' => $companyId,
            'name' => $roleName,
            'guard_name' => 'web',
         ]);

         // Assign permissions
         $permissions = array_map(fn($permName) => $permName, $template['permissions']);
         $role->syncPermissions($permissions);

         $this->command->info("Created role: {$template['display_name']} ({$roleName}) with " . count($template['permissions']) . " permissions");
      }
   }
}
