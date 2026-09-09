<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sales\IndexCustomerRequest;
use App\Http\Requests\Sales\StoreCustomerRequest;
use App\Http\Requests\Sales\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Models\SalesTransaction;
use App\Repositories\CustomerRepository;
use App\Repositories\SalesTransactionRepository;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SalesCustomerController extends Controller
{
    public function __construct(private CustomerRepository $repository, private SalesTransactionRepository $salesRepository, private CompanyContext $companyContext) {}
    public function index(IndexCustomerRequest $request): Response
    {
        $authorization = app(InertiaAuthorizationService::class);
        $authorization->authorize($request->user(), 'penjualan.customers.view');
        $companyId = (string) $this->companyContext->id();
        return Inertia::render('Sales/Customers/Index', ['customerItems' => CustomerResource::collection($this->repository->paginate($companyId, $request->validated())), 'customerFilters' => $request->validated(), 'salesOptions' => ['branches' => $this->salesRepository->branches($companyId)], 'capabilities' => ['create' => $authorization->allows($request->user(), 'penjualan.customers.create'), 'edit' => $authorization->allows($request->user(), 'penjualan.customers.edit'), 'delete' => $authorization->allows($request->user(), 'penjualan.customers.delete')]]);
    }
    public function create(): Response
    {
        Gate::authorize('create', Customer::class);
        return Inertia::render('Sales/Index', ['activeTab' => 'customers', 'customerCreate' => true]);
    }
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        Gate::authorize('create', Customer::class);
        $customer = Customer::create(['company_id' => $request->user()->company_id, ...$request->validated(), 'status' => 'active']);
        return back()->with('success', 'Pelanggan berhasil ditambahkan.')->with('customer', $customer->only('id', 'name', 'telp'));
    }
    public function show(Request $request, Customer $customer): JsonResponse
    {
        Gate::authorize('view', $customer);
        $filters = $request->validate([
            'status' => ['nullable', 'in:draft,completed,return,cancelled'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'per_page' => ['nullable', 'integer', 'in:10,25,50,100'],
        ]);
        $query = SalesTransaction::query()
            ->select(['id', 'customer_id', 'transaction_number', 'transaction_date', 'status', 'payment_status', 'total'])
            ->where('company_id', $this->companyContext->id())
            ->where('customer_id', $customer->getKey())
            ->when($filters['status'] ?? null, fn($builder, $status) => $builder->where('status', $status))
            ->when($filters['date_from'] ?? null, fn($builder, $date) => $builder->whereDate('transaction_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($builder, $date) => $builder->whereDate('transaction_date', '<=', $date));
        $summary = (clone $query)
            ->reorder()
            ->select([])
            ->selectRaw("COALESCE(SUM(total), 0) as total, COALESCE(SUM(CASE WHEN payment_status = 'unpaid' THEN total ELSE 0 END), 0) as receivable, COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) as active")
            ->first();
        $documents = $query->latest('transaction_date')->latest('id')->paginate($filters['per_page'] ?? 10)->through(fn($transaction) => ['number' => $transaction->transaction_number, 'date' => $transaction->transaction_date?->format('d/m/Y'), 'date_iso' => $transaction->transaction_date?->format('Y-m-d'), 'status' => $transaction->status, 'payment_status' => $transaction->payment_status, 'total' => 'Rp ' . number_format($transaction->total, 0, ',', '.'), 'total_amount' => (int) $transaction->total]);
        $customer->load('branch:id,name');
        $data = CustomerResource::make($customer)->resolve($request);
        $data['documents'] = $documents;
        $data['document_summary'] = ['total' => (int) $summary->total, 'receivable' => (int) $summary->receivable, 'active' => (int) $summary->active];

        return response()->json(['data' => $data]);
    }
    public function edit(Customer $customer): Response
    {
        Gate::authorize('update', $customer);
        return Inertia::render('Sales/Index', ['activeTab' => 'customers', 'customer' => CustomerResource::make($customer)->resolve(request())]);
    }
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        Gate::authorize('update', $customer);
        $customer->update($request->validated());
        return back()->with('success', 'Pelanggan berhasil diperbarui.');
    }
    public function destroy(Customer $customer): RedirectResponse
    {
        Gate::authorize('delete', $customer);
        $customer->delete();
        return back()->with('success', 'Pelanggan berhasil dihapus.');
    }
}
