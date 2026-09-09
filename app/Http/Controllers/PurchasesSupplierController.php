<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\IndexSupplierRequest;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Repositories\SupplierRepository;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;
use App\Services\SupplierService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PurchasesSupplierController extends Controller
{
    public function __construct(private SupplierRepository $repository, private SupplierService $service, private CompanyContext $companyContext, private InertiaAuthorizationService $authorization) {}
    public function index(IndexSupplierRequest $request): Response
    {
        $this->authorization->authorize($request->user(), 'suppliers.view');
        return Inertia::render('Purchases/Index', ['activeTab' => 'suppliers', 'cashierLayout' => request()->routeIs('cashier.purchases.*'), 'supplierItems' => SupplierResource::collection($this->repository->paginate((string)$this->companyContext->id(), $request->validated())), 'supplierFilters' => $request->validated(), 'capabilities' => ['create' => $this->authorization->allows($request->user(), 'suppliers.create'), 'edit' => $this->authorization->allows($request->user(), 'suppliers.edit'), 'delete' => $this->authorization->allows($request->user(), 'suppliers.delete')]]);
    }
    public function create(IndexSupplierRequest $request): Response
    {
        return $this->index($request);
    }
    public function store(StoreSupplierRequest $request): RedirectResponse
    {
        Gate::authorize('suppliers.create');
        $supplier = $this->service->create((string)$this->companyContext->id(), (string)$request->user()->id, $request->validated());
        return back()->with('success', 'Supplier berhasil dibuat.')->with('supplier', $supplier->only('id', 'name'));
    }
    public function show(Supplier $supplier): JsonResponse
    {
        Gate::authorize('view', $supplier);
        $filters = request()->validate(['date_from' => ['nullable', 'date'], 'date_to' => ['nullable', 'date', 'after_or_equal:date_from']]);
        $transactions = $supplier->purchaseTransactions()
            ->when($filters['date_from'] ?? null, fn($query, $date) => $query->whereDate('transaction_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn($query, $date) => $query->whereDate('transaction_date', '<=', $date));
        $invoices = (clone $transactions)
            ->select(['id', 'supplier_id', 'transaction_number', 'transaction_date', 'status', 'total'])
            ->latest('transaction_date')
            ->latest('id')
            ->cursorPaginate(20);
        $summary = (clone $transactions)
            ->selectRaw("COALESCE(SUM(total), 0) as purchases, COALESCE(SUM(CASE WHEN status IN ('draft', 'ordered', 'received', 'completed') THEN 1 ELSE 0 END), 0) as active_invoices, COALESCE(SUM(CASE WHEN status IN ('draft', 'ordered', 'received', 'completed') THEN total ELSE 0 END), 0) as active_invoice_value")
            ->first();
        $data = SupplierResource::make($supplier)->resolve(request());
        $data['purchases'] = 'Rp ' . number_format($summary->purchases, 0, ',', '.');
        $data['active'] = $summary->active_invoices . ' Invoice';
        $data['activeInvoiceValue'] = (int) $summary->active_invoice_value;
        $data['invoices'] = [
            'data' => collect($invoices->items())->map(fn($transaction) => ['number' => $transaction->transaction_number, 'date' => $transaction->transaction_date?->format('d/m/Y'), 'total' => 'Rp ' . number_format($transaction->total, 0, ',', '.'), 'status' => $transaction->status, 'variant' => $transaction->status === 'completed' ? 'success' : 'warning'])->values(),
            'next_cursor' => $invoices->nextCursor()?->encode(),
        ];
        return response()->json(['data' => $data]);
    }
    public function edit(Supplier $supplier): Response
    {
        return $this->show($supplier);
    }
    public function detail(Request $request, Supplier $supplier): RedirectResponse
    {
        Gate::authorize('view', $supplier);
        return back()->with('supplierDetail', SupplierResource::make($supplier->load(['purchaseTransactions' => fn($q) => $q->latest('transaction_date')->limit(5)]))->resolve($request));
    }
    public function update(UpdateSupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        Gate::authorize('update', $supplier);
        $this->service->update($supplier, (string)$request->user()->id, $request->validated());
        return back()->with('success', 'Supplier berhasil diperbarui.');
    }
    public function destroy(Request $request, Supplier $supplier): RedirectResponse
    {
        Gate::authorize('delete', $supplier);
        $this->service->delete($supplier, (string)$request->user()->id);
        return back()->with('success', 'Supplier berhasil dihapus.');
    }
}
