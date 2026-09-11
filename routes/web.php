<?php

use App\Http\Controllers\CabangController;
use App\Http\Controllers\CompanyBranchController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyInformationController;
use App\Http\Controllers\CompanyLogoController;
use App\Http\Controllers\CompanyWarehouseController;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CashierDashboardController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\TaxConfigurationController;
use App\Http\Controllers\NumberGeneratorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductMasterManagementController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StoreController;
use App\Http\Middleware\ResolveStore;
use App\Http\Controllers\InventoryOverviewController;
use App\Http\Controllers\InventoryStockController;
use App\Http\Controllers\InventoryBranchProductStockController;
use App\Http\Controllers\InventoryStockAdjustmentController;
use App\Http\Controllers\InventoryProductController;
use App\Http\Controllers\InventoryTransferController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SalesOverviewController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesTransactionController;
use App\Http\Controllers\SalesReturnController;
use App\Http\Controllers\PurchasesTransactionsController;
use App\Http\Controllers\PurchasesSupplierController;
use App\Http\Controllers\PurchasesReturnsController;
use App\Http\Controllers\CashBank\CashBankAccountsController;
use App\Http\Controllers\CashBank\CashBankAccountSettingsController;
use App\Http\Controllers\CashBank\CashBankTransactionsController;
use App\Http\Controllers\CashBank\CashBankTransfersController;
use App\Http\Controllers\Accounting\ShowAccountingOverviewController;
use App\Http\Controllers\Accounting\ShowAccountingReportDataController;
use App\Http\Controllers\Accounting\ShowAccountingReportsController;
use App\Http\Controllers\ReportsSalesController;
use App\Http\Controllers\ReportsPurchasesController;
use App\Http\Controllers\ReportsStockController;
use App\Http\Controllers\ReportsFinanceController;
use App\Http\Controllers\Accounting\ShowAccountingSettingsController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\HR\HRAttendanceController;
use App\Http\Controllers\HR\HRCommissionController;
use App\Http\Controllers\HR\HRPayrollController;
use App\Http\Controllers\HR\HRSummaryController;
use App\Http\Controllers\Api\InventoryMovementController;
use Illuminate\Support\Facades\Route;

Route::get('/', WelcomeController::class)->name('welcome');

// Public Store / Katalog
Route::prefix('store/{company}/{branch}')
    ->middleware(ResolveStore::class)
    ->name('store.')
    ->group(function () {
        Route::get('/', [StoreController::class, 'index'])->name('index');
        Route::get('/catalog', [StoreController::class, 'catalog'])->name('catalog');
        Route::get('/product/{product}', [StoreController::class, 'product'])->name('product');
        Route::get('/cart', [StoreController::class, 'cart'])->name('cart');
        Route::get('/checkout', [StoreController::class, 'checkout'])->name('checkout');
        Route::post('/checkout', [StoreController::class, 'placeOrder'])->name('checkout.store')->middleware('throttle:10,1');
        Route::get('/order-success/{order}', [StoreController::class, 'orderSuccess'])->name('order-success');
    });
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
});

Route::get('/dashboard', DashboardController::class)->middleware(['auth', 'verified', 'onboarded', 'company.context', 'cashier.only'])->name('dashboard');
Route::middleware(['auth', 'cashier.only'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
Route::middleware(['auth', 'verified', 'onboarded', 'company.context', 'cashier.only', 'throttle:60,1'])->group(function () {
    Route::get('users', [UserController::class, 'index'])->middleware('can:users.view')->name('users.index');
    Route::post('users', [UserController::class, 'store'])->middleware('can:users.create')->name('users.store');
    Route::put('users/{user}', [UserController::class, 'update'])->middleware('can:users.edit')->name('users.update');
    Route::patch('users/{user}', [UserController::class, 'update'])->middleware('can:users.edit')->name('users.update.patch');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('can:users.delete')->name('users.destroy');
    Route::get('hr/summary', HRSummaryController::class)->middleware('can:hr.employees.view')->name('hr.summary.index');
    Route::get('hr/employees', [EmployeeController::class, 'index'])->middleware('can:hr.employees.view')->name('hr.employees.index');
    Route::get('hr/attendance', HRAttendanceController::class)->middleware('can:hr.employees.view')->name('hr.attendance.index');
    Route::get('hr/commission', HRCommissionController::class)->middleware('can:hr.employees.view')->name('hr.commission.index');
    Route::get('hr/payroll', HRPayrollController::class)->middleware('can:hr.employees.view')->name('hr.payroll.index');
    Route::post('hr/employees', [EmployeeController::class, 'store'])->middleware('can:hr.employees.create')->name('hr.employees.store');
    Route::put('hr/employees/{employee}', [EmployeeController::class, 'update'])->middleware('can:hr.employees.edit')->name('hr.employees.update');
    Route::delete('hr/employees/{employee}', [EmployeeController::class, 'destroy'])->middleware('can:hr.employees.delete')->name('hr.employees.destroy');
    Route::post('hr/employees/{employee}/reset-pin', [EmployeeController::class, 'resetPin'])->middleware('can:hr.employees.edit')->name('hr.employees.reset-pin');
    Route::get('cabang', [CabangController::class, 'index'])->middleware('can:cabang.view')->name('cabang.index');
    Route::post('cabang', [CabangController::class, 'store'])->middleware('can:cabang.create')->name('cabang.store');
    Route::put('cabang/{cabang}', [CabangController::class, 'update'])->middleware('can:cabang.edit')->name('cabang.update');
    Route::patch('cabang/{cabang}', [CabangController::class, 'update'])->middleware('can:cabang.edit')->name('cabang.update.patch');
    Route::delete('cabang/{cabang}', [CabangController::class, 'destroy'])->middleware('can:cabang.delete')->name('cabang.destroy');
    Route::resource('outlet', OutletController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('gudang', [GudangController::class, 'index'])->middleware('can:gudang.view')->name('gudang.index');
    Route::post('gudang', [GudangController::class, 'store'])->middleware('can:gudang.create')->name('gudang.store');
    Route::put('gudang/{gudang}', [GudangController::class, 'update'])->middleware('can:gudang.edit')->name('gudang.update');
    Route::patch('gudang/{gudang}', [GudangController::class, 'update'])->middleware('can:gudang.edit')->name('gudang.update.patch');
    Route::delete('gudang/{gudang}', [GudangController::class, 'destroy'])->middleware('can:gudang.delete')->name('gudang.destroy');
    Route::resource('chart-of-accounts', ChartOfAccountController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middleware('can:coa.view');
    Route::get('tax-configurations', [TaxConfigurationController::class, 'index'])->middleware('can:taxes.view')->name('tax-configurations.index');
    Route::post('tax-configurations', [TaxConfigurationController::class, 'store'])->middleware('can:taxes.create')->name('tax-configurations.store');
    Route::resource('number-generators', NumberGeneratorController::class)
        ->only(['index', 'store'])
        ->middleware('can:number_generators.view');
    Route::get('products', [ProductController::class, 'index'])->middleware('can:products.view')->name('products.index');
    Route::post('products', [ProductController::class, 'store'])->middleware('can:products.create')->name('products.store');
    Route::put('products/{product}', [ProductController::class, 'update'])->middleware('can:products.edit')->name('products.update');
    Route::patch('products/{product}', [ProductController::class, 'update'])->middleware('can:products.edit')->name('products.update.patch');
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->middleware('can:products.delete')->name('products.destroy');
    Route::get('suppliers', [SupplierController::class, 'index'])->middleware('can:suppliers.view')->name('suppliers.index');
    Route::post('suppliers', [SupplierController::class, 'store'])->middleware('can:suppliers.create')->name('suppliers.store');
    Route::put('suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('can:suppliers.edit')->name('suppliers.update');
    Route::patch('suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('can:suppliers.edit')->name('suppliers.update.patch');
    Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->middleware('can:suppliers.delete')->name('suppliers.destroy');
    Route::get('inventory', [InventoryOverviewController::class, 'index'])->middleware('can:inventory.summary.view')->name('inventory.index');
    Route::get('cashier/inventory', [InventoryOverviewController::class, 'index'])->middleware('can:inventory.summary.view')->name('cashier.inventory');
    Route::get('inventory/products/{stockId}/detail', [InventoryOverviewController::class, 'productDetail'])->middleware('can:inventory.summary.view')->name('inventory.products.detail');
    Route::get('inventory/stock', [InventoryStockController::class, 'index'])->middleware('can:inventory.stock.view')->name('inventory.stock');
    Route::get('inventory/movements', [InventoryMovementController::class, 'page'])->middleware('can:inventory.movements.view')->name('inventory.movements');
    Route::get('cashier/inventory/stock', [InventoryStockController::class, 'index'])->middleware('can:inventory.stock.view')->name('cashier.inventory.stock');
    Route::get('inventory/transfers', [InventoryTransferController::class, 'index'])->middleware('can:inventory.transfers.view')->name('inventory.transfers');
    Route::get('cashier/inventory/transfers', [InventoryTransferController::class, 'index'])->middleware('can:inventory.transfers.view')->name('cashier.inventory.transfers');
    Route::get('inventory/transfers/{transfer}', [InventoryTransferController::class, 'show'])->middleware('can:inventory.transfers.view')->name('inventory.transfers.show');
    Route::post('inventory/transfers', [InventoryTransferController::class, 'store'])->middleware('can:inventory.transfers.create')->name('inventory.transfers.store');
    Route::post('inventory/transfers/{transfer}/receive', [InventoryTransferController::class, 'receive'])->middleware('can:inventory.transfers.receive')->name('inventory.transfers.receive');
    Route::get('inventory/stock-opnames', [StockOpnameController::class, 'index'])->middleware('can:inventory.opname.view')->name('inventory.opnames');
    Route::get('cashier/inventory/stock-opnames', [StockOpnameController::class, 'index'])->middleware('can:inventory.opname.view')->name('cashier.inventory.opnames');
    Route::post('inventory/stock-opnames', [StockOpnameController::class, 'store'])->middleware('can:inventory.opname.create')->name('inventory.opnames.store');
    Route::get('inventory/stock-opnames/{opname}/detail', [StockOpnameController::class, 'detail'])->middleware('can:inventory.opname.view')->name('inventory.opnames.detail');
    Route::delete('inventory/stock-opnames/{opname}', [StockOpnameController::class, 'destroy'])->middleware('can:inventory.opname.delete')->name('inventory.opnames.destroy');
    Route::get('inventory/stock-opname/{opname}', [StockOpnameController::class, 'show'])->middleware('can:inventory.opname.view')->name('inventory.opname.show');
    Route::put('inventory/stock-opname/{opname}', [StockOpnameController::class, 'update'])->middleware('can:inventory.opname.edit')->name('inventory.opname.update');
    Route::post('inventory/stock-opname/{opname}/complete', [StockOpnameController::class, 'complete'])->middleware('can:inventory.opname.process')->name('inventory.opname.complete');
    Route::post('inventory/products', [InventoryProductController::class, 'store'])->middleware('can:inventory.stock.create')->name('inventory.products.store');
    Route::put('inventory/products/{product_stock}', [InventoryProductController::class, 'update'])->middleware('can:inventory.stock.edit')->name('inventory.products.update');
    Route::put('inventory/branch-products/{branch_product_stock}/discount', [InventoryBranchProductStockController::class, 'updateDiscount'])->middleware('can:inventory.summary.discount')->name('inventory.branch-product-stocks.discount.update');
    Route::post('inventory/adjustments', [InventoryStockAdjustmentController::class, 'store'])->middleware('can:inventory.summary.adjust')->name('inventory.adjustments.store');
    Route::delete('inventory/products/{product_stock}', [InventoryProductController::class, 'destroy'])->middleware('can:inventory.stock.delete')->name('inventory.products.destroy');
    Route::prefix('api/inventory')->name('api.inventory.')->group(function () {
        Route::get('movements', [InventoryMovementController::class, 'index'])->name('movements.index');
        Route::get('products/{product}/movements', [InventoryMovementController::class, 'product'])->name('products.movements');
        Route::post('reconcile/{product}', [InventoryMovementController::class, 'reconcile'])->name('reconcile');
    });
    Route::get('cashier/product', [ProductController::class, 'cashier'])->middleware('can:products.view')->name('cashier.products');
    Route::get('cashier/dashboard', CashierDashboardController::class)->middleware('can:cashier.access')->name('cashier.dashboard');
    Route::get('sales', [SalesOverviewController::class, 'index'])->middleware('can:penjualan.summary.view')->name('sales.index');
    Route::get('cashier/sales', SalesController::class)->middleware('can:penjualan.summary.view')->name('cashier.sales');
    Route::get('cashier/sales/{salesTab}', SalesController::class)
        ->whereIn('salesTab', ['overview', 'transactions', 'returns', 'customers'])
        ->middleware('can:penjualan.summary.view')
        ->name('cashier.sales.tab');
    Route::get('cashier', [SalesTransactionController::class, 'cashier'])->middleware('can:cashier.access')->name('cashier');
    Route::get('sales/transactions', [SalesTransactionController::class, 'index'])->middleware('can:penjualan.transactions.view')->name('sales.transactions.index');
    Route::get('sales/transactions/create', [SalesTransactionController::class, 'create'])->middleware('can:penjualan.transactions.create')->name('sales.transactions.create');
    Route::post('sales/transactions', [SalesTransactionController::class, 'store'])->middleware('can:penjualan.transactions.create')->name('sales.transactions.store');
    Route::get('sales/transactions/{transaction}/print', [SalesTransactionController::class, 'print'])->middleware('can:penjualan.transactions.print')->name('sales.transactions.print');
    Route::get('sales/transactions/{transaction}', [SalesTransactionController::class, 'show'])->middleware('can:penjualan.transactions.view')->name('sales.transactions.show');
    Route::get('sales/transactions/{transaction}/edit', [SalesTransactionController::class, 'edit'])->middleware('can:penjualan.transactions.edit')->name('sales.transactions.edit');
    Route::put('sales/transactions/{transaction}', [SalesTransactionController::class, 'update'])->middleware('can:penjualan.transactions.edit')->name('sales.transactions.update');
    Route::patch('sales/transactions/{transaction}', [SalesTransactionController::class, 'update'])->middleware('can:penjualan.transactions.edit')->name('sales.transactions.update.patch');
    Route::delete('sales/transactions/{transaction}', [SalesTransactionController::class, 'destroy'])->middleware('can:penjualan.transactions.delete')->name('sales.transactions.destroy');
    Route::post('sales/transactions/{transaction}/finalize', [SalesTransactionController::class, 'finalize'])->middleware('can:penjualan.transactions.edit')->name('sales.transactions.finalize');
    Route::post('sales/transactions/{transaction}/payments', [SalesTransactionController::class, 'payment'])->middleware('can:penjualan.transactions.payment')->name('sales.transactions.payments.store');
    Route::post('sales/transactions/{transaction}/complete', [SalesTransactionController::class, 'complete'])->middleware('can:penjualan.transactions.edit')->name('sales.transactions.complete');
    Route::post('sales/transactions/{transaction}/returns', [SalesTransactionController::class, 'return'])->middleware('can:penjualan.transactions.return')->name('sales.transactions.returns.store');
    Route::post('sales/returns/{return}/complete', [SalesTransactionController::class, 'completeReturn'])->middleware('can:penjualan.transactions.return')->name('sales.returns.complete');
    Route::delete('sales/returns/{return}', [SalesTransactionController::class, 'destroyReturn'])->middleware('can:penjualan.transactions.return')->name('sales.returns.destroy');
    Route::get('sales/customers', [CustomerController::class, 'index'])->middleware('can:penjualan.customers.view')->name('sales.customers.index');
    Route::get('sales/customers/create', [CustomerController::class, 'create'])->middleware('can:penjualan.customers.create')->name('sales.customers.create');
    Route::post('sales/customers', [CustomerController::class, 'store'])->middleware('can:penjualan.customers.create')->name('sales.customers.store');
    Route::get('sales/customers/{customer}', [CustomerController::class, 'show'])->middleware('can:penjualan.customers.view')->name('sales.customers.show');
    Route::get('sales/customers/{customer}/edit', [CustomerController::class, 'edit'])->middleware('can:penjualan.customers.edit')->name('sales.customers.edit');
    Route::put('sales/customers/{customer}', [CustomerController::class, 'update'])->middleware('can:penjualan.customers.edit')->name('sales.customers.update');
    Route::patch('sales/customers/{customer}', [CustomerController::class, 'update'])->middleware('can:penjualan.customers.edit')->name('sales.customers.update.patch');
    Route::delete('sales/customers/{customer}', [CustomerController::class, 'destroy'])->middleware('can:penjualan.customers.delete')->name('sales.customers.destroy');
    Route::get('sales/returns', [SalesReturnController::class, 'index'])->middleware('can:penjualan.returns.view')->name('sales.returns.index');
    Route::get('sales/returns/{return}', [SalesReturnController::class, 'show'])->middleware('can:penjualan.returns.view')->name('sales.returns.show');
    Route::resource('purchases/transactions', PurchasesTransactionsController::class)->names('purchases.transactions')->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])->middleware('can:pembelian.view');
    Route::post('purchases/transactions/{transaction}/payments', [PurchasesTransactionsController::class, 'payment'])->middleware('can:pembelian.pay')->name('purchases.transactions.payments.store');
    Route::post('purchases/transactions/{transaction}/complete', [PurchasesTransactionsController::class, 'complete'])->middleware('can:pembelian.complete')->name('purchases.transactions.complete');
    Route::post('purchases/transactions/{transaction}/returns', [PurchasesTransactionsController::class, 'return'])->middleware('can:pembelian.return')->name('purchases.transactions.returns.store');
    Route::get('purchases/transactions/{transaction}/refund-accounts', [PurchasesTransactionsController::class, 'refundAccounts'])->middleware('can:pembelian.return')->name('purchases.transactions.refund-accounts');
    Route::resource('purchases/supplier', PurchasesSupplierController::class)->names('purchases.suppliers')->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])->middleware('can:suppliers.view');
    Route::get('purchases/supplier/detail/{supplier}', [PurchasesSupplierController::class, 'detail'])->middleware('can:suppliers.view')->name('purchases.suppliers.detail');
    Route::resource('purchases/returns', PurchasesReturnsController::class)->names('purchases.returns')->only(['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'])->middleware('can:pembelian.returns.view');
    Route::get('purchases', [PurchasesTransactionsController::class, 'index'])->middleware('can:pembelian.view')->name('purchases.index');
    Route::get('cashier/purchases', [PurchasesTransactionsController::class, 'index'])->middleware('can:pembelian.view')->name('cashier.purchases');
    Route::get('cashier/purchases/suppliers', [PurchasesSupplierController::class, 'index'])->middleware('can:suppliers.view')->name('cashier.purchases.suppliers');
    Route::get('cashier/purchases/returns', [PurchasesReturnsController::class, 'index'])->middleware('can:pembelian.returns.view')->name('cashier.purchases.returns');
    Route::redirect('cash-bank', 'cash-bank/transactions')->middleware('can:cash_bank.view')->name('cash-bank.index');
    Route::get('cash-bank/transactions', [CashBankTransactionsController::class, 'index'])->middleware('can:cash_bank.transactions.view')->name('cash-bank.transactions.index');
    Route::post('cash-bank/transactions', [CashBankTransactionsController::class, 'store'])->middleware('can:cash_bank.transactions.create')->name('cash-bank.transactions.store');
    Route::put('cash-bank/transactions/{transaction}', [CashBankTransactionsController::class, 'update'])->middleware('can:cash_bank.transactions.edit')->name('cash-bank.transactions.update');
    Route::delete('cash-bank/transactions/{transaction}', [CashBankTransactionsController::class, 'destroy'])->middleware('can:cash_bank.transactions.delete')->name('cash-bank.transactions.destroy');
    Route::get('cash-bank/accounts', [CashBankAccountsController::class, 'index'])->middleware('can:cash_bank.accounts.view')->name('cash-bank.accounts.index');
    Route::get('cash-bank/accounts/list', [CashBankAccountsController::class, 'list'])->middleware('can:cash_bank.accounts.view')->name('cash-bank.accounts.list');
    Route::get('cash-bank/accounts/search', [CashBankAccountsController::class, 'search'])->middleware('can:cash_bank.accounts.view')->name('cash-bank.accounts.search');
    Route::post('cash-bank/accounts', [CashBankAccountsController::class, 'store'])->middleware('can:cash_bank.accounts.create')->name('cash-bank.accounts.store');
    Route::put('cash-bank/accounts/{account}', [CashBankAccountsController::class, 'update'])->middleware('can:cash_bank.accounts.edit')->name('cash-bank.accounts.update');
    Route::patch('cash-bank/accounts/{account}/deactivate', [CashBankAccountsController::class, 'deactivate'])->middleware('can:cash_bank.accounts.deactivate')->name('cash-bank.accounts.deactivate');
    Route::delete('cash-bank/accounts/{account}', [CashBankAccountsController::class, 'destroy'])->middleware('can:cash_bank.accounts.delete')->name('cash-bank.accounts.destroy');
    Route::apiResource('cash-bank/account-settings', CashBankAccountSettingsController::class)->names('cash-bank.account-settings')->middleware('can:cash_bank.settings.view');
    Route::get('cash-bank/transfers', [CashBankTransfersController::class, 'index'])->middleware('can:cash_bank.transfers.view')->name('cash-bank.transfers.index');
    Route::post('cash-bank/transfers', [CashBankTransfersController::class, 'store'])->middleware('can:cash_bank.transfers.create')->name('cash-bank.transfers.store');
    Route::get('accounting', ShowAccountingOverviewController::class)
        ->middleware('can:accounting.view')
        ->name('accounting.index');
    Route::get('accounting/reports', ShowAccountingReportsController::class)
        ->middleware('can:laporan.view')
        ->name('accounting.reports.index');
    Route::get('reports/sales', [ReportsSalesController::class, 'index'])
        ->middleware('can:laporan.view')
        ->name('reports.sales');
    Route::get('reports/sales/export', [ReportsSalesController::class, 'export'])
        ->middleware('can:laporan.view')
        ->name('reports.sales.export');
    Route::get('reports/sales/transactions/{transaction}', [ReportsSalesController::class, 'show'])
        ->middleware('can:laporan.view')
        ->name('reports.sales.detail');
    Route::get('reports/purchases', [ReportsPurchasesController::class, 'index'])
        ->middleware('can:laporan.view')
        ->name('reports.purchases');
    Route::get('reports/purchases/export', [ReportsPurchasesController::class, 'export'])
        ->middleware('can:laporan.view')
        ->name('reports.purchases.export');
    Route::get('reports/purchases/transactions/{transaction}', [ReportsPurchasesController::class, 'show'])
        ->middleware('can:laporan.view')
        ->name('reports.purchases.detail');
    Route::get('cashier/reports/purchases', [ReportsPurchasesController::class, 'index'])
        ->middleware('can:laporan.view')
        ->name('cashier.reports.purchases');
    Route::get('reports/stock', [ReportsStockController::class, 'index'])
        ->middleware('can:laporan.view')
        ->name('reports.stock');
    Route::get('reports/stock/export', [ReportsStockController::class, 'export'])
        ->middleware('can:laporan.view')
        ->name('reports.stock.export');
    Route::get('reports/stock/options', [ReportsStockController::class, 'options'])
        ->middleware('can:laporan.view')
        ->name('reports.stock.options');
    Route::get('reports/stock/products/{product}', [ReportsStockController::class, 'show'])
        ->middleware('can:laporan.view')
        ->name('reports.stock.detail');
    Route::get('cashier/reports/stock', [ReportsStockController::class, 'index'])
        ->middleware('can:laporan.view')
        ->name('cashier.reports.stock');
    Route::get('reports/finance', [ReportsFinanceController::class, 'index'])
        ->middleware('can:laporan.view')
        ->name('reports.finance');
    Route::get('reports/finance/export', [ReportsFinanceController::class, 'export'])
        ->middleware('can:laporan.view')
        ->name('reports.finance.export');
    Route::get('reports/finance/transactions/{transaction}', [ReportsFinanceController::class, 'show'])
        ->middleware('can:laporan.view')
        ->name('reports.finance.detail');
    Route::get('cashier/reports/finance', [ReportsFinanceController::class, 'index'])
        ->middleware('can:laporan.view')
        ->name('cashier.reports.finance');
    Route::get('cashier/reports', [ReportsSalesController::class, 'index'])
        ->middleware('can:laporan.view')
        ->name('cashier.reports');
    Route::get('accounting/reports/{report}', ShowAccountingReportDataController::class)
        ->middleware('can:laporan.view')
        ->name('accounting.reports.data');
    Route::get('accounting/settings', ShowAccountingSettingsController::class)
        ->middleware('can:coa.view')
        ->name('accounting.settings.index');
    Route::get('products/master/{master}', [ProductMasterManagementController::class, 'index'])->name('products.master.index');
    Route::get('cashier/product/{master}', [ProductMasterManagementController::class, 'cashier'])
        ->whereIn('master', ['product_categories', 'product_brands', 'units'])
        ->name('cashier.product.master');
    Route::post('products/master/{master}', [ProductMasterManagementController::class, 'store'])->name('products.master.store');
    Route::put('products/master/{master}/{item}', [ProductMasterManagementController::class, 'update'])->name('products.master.update');
    Route::delete('products/master/{master}/{item}', [ProductMasterManagementController::class, 'destroy'])->name('products.master.destroy');
    Route::get('roles', [RoleController::class, 'index'])->middleware('can:roles.view')->name('roles.index');
    Route::post('roles', [RoleController::class, 'store'])->middleware('can:roles.create')->name('roles.store');
    Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('can:roles.edit')->name('roles.update');
    Route::patch('roles/{role}', [RoleController::class, 'update'])->middleware('can:roles.edit')->name('roles.update.patch');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('can:roles.delete')->name('roles.destroy');
    Route::resource('permissions', PermissionController::class)->only(['index', 'store']);

    // Reminders
    Route::get('reminders', [ReminderController::class, 'index'])->middleware('can:reminders.view')->name('reminders.index');
    Route::post('reminders', [ReminderController::class, 'store'])->middleware('can:reminders.create')->name('reminders.store');
    Route::put('reminders/{reminder}', [ReminderController::class, 'update'])->middleware('can:reminders.edit')->name('reminders.update');
    Route::patch('reminders/{reminder}', [ReminderController::class, 'update'])->middleware('can:reminders.edit')->name('reminders.update.patch');
    Route::delete('reminders/{reminder}', [ReminderController::class, 'destroy'])->middleware('can:reminders.delete')->name('reminders.destroy');
    Route::post('reminders/{reminder}/complete', [ReminderController::class, 'complete'])->middleware('can:reminders.edit')->name('reminders.complete');
    Route::post('reminders/{reminder}/cancel', [ReminderController::class, 'cancel'])->middleware('can:reminders.edit')->name('reminders.cancel');

    Route::get('company', [CompanyInformationController::class, 'index'])->middleware('can:company.view')->name('company.edit');
    Route::get('company/cabang', fn() => redirect()->route('company.branches', request()->query()));
    Route::get('company/gudang', fn() => redirect()->route('company.warehouses', request()->query()));
    Route::get('company/branches', [CompanyBranchController::class, 'index'])->middleware('can:cabang.view')->name('company.branches');
    Route::get('company/warehouses', [CompanyWarehouseController::class, 'index'])->middleware('can:gudang.view')->name('company.warehouses');
    Route::get('company/logo', [CompanyLogoController::class, 'index'])->middleware('can:company.logo')->name('company.logo');
    Route::get('company/features', [App\Http\Controllers\Company\CompanyFeaturesController::class, 'index'])->middleware('can:company.settings')->name('company.features');
    Route::put('company/features', [App\Http\Controllers\Company\CompanyFeaturesController::class, 'update'])->middleware('can:company.settings')->name('company.features.update');
    Route::get('search/cabang', [SearchController::class, 'cabang'])->name('search.cabang');
    Route::get('search/gudang', [SearchController::class, 'gudang'])->name('search.gudang');
    Route::get('search/suppliers', [SearchController::class, 'suppliers'])->name('search.suppliers');
    Route::get('search/purchase-products', [SearchController::class, 'purchaseProducts'])->name('search.purchase-products');
    Route::get('search/product-categories', [SearchController::class, 'productCategories'])->name('search.product-categories');
    Route::get('search/product-brands', [SearchController::class, 'productBrands'])->name('search.product-brands');
    Route::get('search/units', [SearchController::class, 'units'])->name('search.units');
    Route::get('search/products', [SearchController::class, 'products'])->name('search.products');
    Route::get('search/branch-products', [SearchController::class, 'branchProducts'])->name('search.branch-products');
    Route::get('search/customers', [SearchController::class, 'customers'])->name('search.customers');
    Route::put('company/{company}', [CompanyController::class, 'update'])->middleware('can:company.edit')->name('company.update');
    Route::put('company/{company}/settings', [CompanyController::class, 'updateSettings'])->middleware('can:company.settings')->name('company.settings.update');
    Route::post('company/{company}/logo', [CompanyController::class, 'uploadLogo'])->middleware('can:company.logo')->name('company.logo.store');
    Route::delete('company/{company}/logo', [CompanyController::class, 'destroyLogo'])->middleware('can:company.logo')->name('company.logo.destroy');

    // E-Commerce Settings
    Route::redirect('ecommerce', 'ecommerce/settings/editor')->name('ecommerce.index');
    Route::get('ecommerce/settings/editor', [App\Http\Controllers\Company\Store\StoreSettingsController::class, 'editor'])->middleware('can:store.settings.view')->name('ecommerce.settings.editor');
    Route::put('ecommerce/settings/sections', [App\Http\Controllers\Company\Store\StoreSettingsController::class, 'updateSections'])->middleware('can:store.settings.edit')->name('ecommerce.settings.sections.update');
    Route::get('ecommerce/settings/appearance', [App\Http\Controllers\Company\Store\StoreSettingsController::class, 'appearance'])->middleware('can:store.settings.view')->name('ecommerce.settings.appearance');
    Route::put('ecommerce/settings/appearance', [App\Http\Controllers\Company\Store\StoreSettingsController::class, 'updateAppearance'])->middleware('can:store.settings.edit')->name('ecommerce.settings.appearance.update');
    Route::get('ecommerce/settings/payment', [App\Http\Controllers\Company\Store\StoreSettingsController::class, 'payment'])->middleware('can:store.settings.view')->name('ecommerce.settings.payment');
    Route::put('ecommerce/settings/payment', [App\Http\Controllers\Company\Store\StoreSettingsController::class, 'updatePayment'])->middleware('can:store.settings.edit')->name('ecommerce.settings.payment.update');
    Route::get('ecommerce/settings/shipping', [App\Http\Controllers\Company\Store\StoreSettingsController::class, 'shipping'])->middleware('can:store.settings.view')->name('ecommerce.settings.shipping');
    Route::put('ecommerce/settings/shipping', [App\Http\Controllers\Company\Store\StoreSettingsController::class, 'updateShipping'])->middleware('can:store.settings.edit')->name('ecommerce.settings.shipping.update');
    Route::get('ecommerce/settings/domain', [App\Http\Controllers\Company\Store\StoreSettingsController::class, 'domain'])->middleware('can:store.settings.view')->name('ecommerce.settings.domain');
    Route::put('ecommerce/settings/domain', [App\Http\Controllers\Company\Store\StoreSettingsController::class, 'updateDomain'])->middleware('can:store.settings.edit')->name('ecommerce.settings.domain.update');
});
require __DIR__ . '/auth.php';
