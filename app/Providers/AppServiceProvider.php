<?php

namespace App\Providers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Gudang;
use App\Models\Outlet;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Models\Supplier;
use App\Models\ProductStock;
use App\Models\TransferTransaction;
use App\Models\StockOpname;
use App\Models\SalesTransaction;
use App\Models\SalesReturn;
use App\Models\Customer;
use App\Models\CashBankAccountSetting;
use App\Models\Reminder;
use App\Policies\ReminderPolicy;
use App\Policies\ProductBrandPolicy;
use App\Policies\ProductCategoryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\UnitPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\ProductStockPolicy;
use App\Policies\TransferTransactionPolicy;
use App\Policies\StockOpnamePolicy;
use App\Policies\SalesTransactionPolicy;
use App\Policies\SalesReturnPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\CashBankAccountSettingPolicy;
use App\Models\ChartOfAccount;
use App\Models\TaxConfiguration;
use App\Models\NumberGenerator;
use App\Policies\ChartOfAccountPolicy;
use App\Policies\TaxConfigurationPolicy;
use App\Policies\NumberGeneratorPolicy;
use App\Policies\CabangPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\GudangPolicy;
use App\Policies\OutletPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(
            CompanyContext::class,
            fn (): CompanyContext => new CompanyContext(),
        );
        $this->app->scoped(InertiaAuthorizationService::class);
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);
        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(Branch::class, CabangPolicy::class);
        Gate::policy(Outlet::class, OutletPolicy::class);
        Gate::policy(Gudang::class, GudangPolicy::class);
        Gate::policy(ChartOfAccount::class, ChartOfAccountPolicy::class);
        Gate::policy(TaxConfiguration::class, TaxConfigurationPolicy::class);
        Gate::policy(NumberGenerator::class, NumberGeneratorPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(ProductCategory::class, ProductCategoryPolicy::class);
        Gate::policy(ProductBrand::class, ProductBrandPolicy::class);
        Gate::policy(Unit::class, UnitPolicy::class);
        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(ProductStock::class, ProductStockPolicy::class);
        Gate::policy(TransferTransaction::class, TransferTransactionPolicy::class);
        Gate::policy(StockOpname::class, StockOpnamePolicy::class);
        Gate::policy(SalesTransaction::class, SalesTransactionPolicy::class);
        Gate::policy(SalesReturn::class, SalesReturnPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(CashBankAccountSetting::class, CashBankAccountSettingPolicy::class);
        Gate::policy(Reminder::class, ReminderPolicy::class);

        Vite::prefetch(concurrency: 3);
    }
}
