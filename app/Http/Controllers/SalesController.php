<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;
use App\Http\Requests\Sales\StoreSalesTransactionRequest;
use App\Http\Requests\Sales\UpdateSalesTransactionRequest;
use App\Http\Requests\Sales\StoreCustomerRequest;
use App\Http\Requests\Sales\UpdateCustomerRequest;
use App\Http\Requests\Sales\IndexSalesTransactionRequest;
use App\Http\Resources\CustomerResource;
use App\Repositories\CustomerRepository;
use App\Models\Customer;
use App\Http\Resources\SalesTransactionResource;
use App\Repositories\SalesTransactionRepository;
use App\Services\CompanyContext;
use App\Services\SalesTransactionService;
use App\Models\SalesTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\JsonResponse;

class SalesController extends Controller
{
    public function __construct(private SalesTransactionRepository $repository, private SalesTransactionService $service, private CompanyContext $companyContext, private CustomerRepository $customerRepository) {}

    public function __invoke(IndexSalesTransactionRequest $request): Response
    {
        $tab = $request->route('salesTab', 'overview');
        $transactionFilters = $request->validated();
        return Inertia::render('Sales/Index', [
            'activeTab' => $tab,
            'cashierLayout' => $request->routeIs('cashier.sales*'),
            'salesRoutes' => $request->routeIs('cashier.sales*') ? [
                'overview' => ['cashier.sales', []],
                'transactions' => ['cashier.sales.tab', ['salesTab' => 'transactions']],
                'returns' => ['cashier.sales.tab', ['salesTab' => 'returns']],
                'customers' => ['cashier.sales.tab', ['salesTab' => 'customers']],
            ] : [],
            'transactionItems' => $tab === 'transactions' ? $this->repository->paginate((string) $this->companyContext->id(), $transactionFilters)->through(fn($item) => SalesTransactionResource::make($item)->resolve($request)) : null,
            'transactionFilters' => $tab === 'transactions' ? $transactionFilters : [],
            'salesOptions' => in_array($tab, ['transactions', 'customers'], true) ? ['branches' => $this->repository->branches((string) $this->companyContext->id())] : null,
            'customerItems' => $tab === 'customers' ? CustomerResource::collection($this->customerRepository->paginate((string) $this->companyContext->id(), $request->only('search', 'per_page', 'cursor'))) : null,
            'customerFilters' => $tab === 'customers' ? $request->only('search', 'per_page') : [],
            'summary' => $tab === 'overview' ? $this->repository->summaryStats((string) $this->companyContext->id()) : null,
            'chartData' => $tab === 'overview' ? $this->repository->chartData((string) $this->companyContext->id(), $request->query('period', 'month')) : [],
            'activities' => $tab === 'overview' ? $this->repository->recentActivities((string) $this->companyContext->id(), 5)->map(fn($t) => [
                'id' => $t->id,
                'number' => $t->transaction_number,
                'customer' => $t->customer?->name ?? 'Penjualan Umum',
                'date' => $t->created_at?->format('d/m/Y H:i'),
                'amount' => (int) $t->total,
                'status' => $t->payment_status === 'paid' ? 'Lunas' : 'Belum Dibayar',
                'variant' => $t->payment_status === 'paid' ? 'success' : 'warning',
                'icon' => $t->payment_status === 'paid' ? 'BadgeCheck' : 'FilePlus2',
                'type' => $t->status === 'completed' ? 'Invoice' : 'Draft',
            ])->values() : [],
            'topCustomers' => $tab === 'overview' ? $this->repository->topCustomers((string) $this->companyContext->id(), 5)->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'total_spent' => (int) $c->total_spent,
                'transaction_count' => (int) $c->transaction_count,
            ])->values() : [],
            'attentionDocs' => $tab === 'overview' ? $this->repository->attentionDocs((string) $this->companyContext->id()) : [],
        ]);
    }

    public function store(StoreSalesTransactionRequest $request): RedirectResponse
    {
        Gate::authorize('create', SalesTransaction::class);
        $this->service->create((string) $this->companyContext->id(), (string) $request->user()->id, $request->validated());
        return back()->with('success', 'Transaksi penjualan berhasil disimpan.');
    }

    public function destroy(SalesTransaction $transaction): RedirectResponse
    {
        Gate::authorize('delete', $transaction);
        $this->service->delete($transaction->load('details'));
        return back()->with('success', 'Transaksi penjualan dibatalkan.');
    }

    public function update(UpdateSalesTransactionRequest $request, SalesTransaction $transaction): RedirectResponse
    {
        Gate::authorize('update', $transaction);
        $this->service->update($transaction, $request->validated());
        return back()->with('success', 'Transaksi penjualan berhasil diperbarui.');
    }

    public function storeCustomer(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create(['company_id' => $request->user()->company_id, ...$request->validated(), 'status' => $request->validated('status', 'active')]);
        return back()->with('success', 'Pelanggan berhasil ditambahkan.')->with('customer', $customer->only('id', 'name', 'telp'));
    }

    public function updateCustomer(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        Gate::authorize('view', $customer);
        $customer->update($request->validated());
        return back()->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function destroyCustomer(Customer $customer): RedirectResponse
    {
        Gate::authorize('delete', $customer);
        $customer->delete();
        return back()->with('success', 'Pelanggan berhasil dihapus.');
    }

    public function customerDetails(Customer $customer): JsonResponse
    {
        Gate::authorize('update', $customer);
        $customer->load(['branch:id,name', 'salesTransactions' => fn($query) => $query->latest('transaction_date')]);
        return response()->json(CustomerResource::make($customer)->resolve(request()));
    }
}
