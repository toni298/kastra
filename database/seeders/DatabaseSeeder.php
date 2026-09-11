<?php

namespace Database\Seeders;

use App\Services\RbacService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(RbacService $rbac): void
    {
        $rbac->bootstrap();

        $this->call(\Database\Seeders\AccountingPermissionSeeder::class);
        $this->call(\Database\Seeders\CashierPermissionSeeder::class);
        $this->call(\Database\Seeders\CashBankPermissionSeeder::class);
        $this->call(\Database\Seeders\CompanyPermissionSeeder::class);
        $this->call(\Database\Seeders\InventoryPermissionSeeder::class);
        $this->call(\Database\Seeders\ProductPermissionSeeder::class);
        $this->call(\Database\Seeders\RolePermissionSeeder::class);
        $this->call(\Database\Seeders\UserPermissionSeeder::class);
        $this->call(\Database\Seeders\PurchasesPermissionSeeder::class);
        $this->call(\Database\Seeders\SalesPermissionSeeder::class);
        $this->call(\Database\Seeders\ReminderPermissionSeeder::class);
        $this->call(\Database\Seeders\HrPermissionSeeder::class);
        $this->call(PermissionGroupSeeder::class);
        $this->call(SupplierPermissionSeeder::class);
        // $this->call(ProductStockSeeder::class); // Requires RetailProductSeeder
        // $this->call(CashBankTransactionPerformanceSeeder::class);
        // $this->call(CashBankTransferPerformanceSeeder::class);
    }
}
