<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sales\StoreSalesTransactionRequest;
use App\Http\Requests\Sales\IndexSalesTransactionRequest;
use App\Http\Requests\Sales\IndexCashierRequest;
use App\Http\Requests\Sales\UpdateSalesTransactionRequest;
use App\Http\Requests\Sales\StoreSalesPaymentRequest;
use App\Http\Requests\Sales\StoreSalesReturnRequest;
use App\Models\SalesReturn;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\TaxConfiguration;
use App\Http\Resources\SalesTransactionResource;
use App\Models\SalesTransaction;
use App\Repositories\SalesTransactionRepository;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;
use App\Services\SalesTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class SalesTransactionController extends Controller
{
    public function __construct(private SalesTransactionRepository $repository, private SalesTransactionService $service, private CompanyContext $companyContext) {}

    public function index(IndexSalesTransactionRequest $request): Response
    {
        $authorization = app(InertiaAuthorizationService::class);
        $authorization->authorize($request->user(), 'penjualan.transactions.view');
        $companyId = (string) $this->companyContext->id();
        return Inertia::render('Sales/Transactions/Index', [
            'transactionItems' => SalesTransactionResource::collection(
                $this->repository->paginate($companyId, $request->validated())
            ),
            'transactionFilters' => $request->validated(),
            'salesOptions' => ['branches' => $this->repository->branches($companyId)],
            'capabilities' => [
                'create' => $authorization->allows($request->user(), 'penjualan.transactions.create'),
                'edit' => $authorization->allows($request->user(), 'penjualan.transactions.edit'),
                'delete' => $authorization->allows($request->user(), 'penjualan.transactions.delete'),
            ],
        ]);
    }

    public function cashier(IndexCashierRequest $request): Response
    {
        Gate::authorize('create', SalesTransaction::class);
        $companyId = (string) $this->companyContext->id();
        $branches = Branch::query()
            ->where('company_id', $companyId)
            ->where('status', Branch::STATUS_ACTIVE)
            ->when($request->user()->branch_id, fn($query, $branchId) => $query->whereKey($branchId))
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get(['id', 'name']);
        $branchId = $branches->firstWhere('id', $request->validated()['branch_id'] ?? null)?->id
            ?? $branches->first()?->id;
        $filters = $request->validated();
        $taxConfiguration = TaxConfiguration::query()
            ->where('company_id', $companyId)
            ->where('jenis', 'penjualan')
            ->where('aktif', true)
            ->orderByDesc('created_at')
            ->first(['persentase', 'mode', 'nama']);

        $products = Product::query()
            ->with(['category:id,name', 'images:id,product_id,webp_path,thumbnail_path,original_path'])
            ->withSum(['branchStocks as stock' => fn($query) => $query->where('branch_id', $branchId)], 'quantity')
            ->withMax(['branchStocks as discount' => fn($query) => $query
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)], 'discount')
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->whereHas('branchStocks', fn($stockQuery) => $stockQuery
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('quantity', '>', 0))
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $search = addcslashes(trim((string) $search), "\\%_");

                $query->where(fn($nested) => $nested
                    ->where('name', 'like', "{$search}%")
                    ->orWhere('sku', 'like', "{$search}%")
                    ->orWhere('barcode', 'like', "{$search}%"));
            })
            ->when($filters['category_id'] ?? null, function ($query, $categoryId) {
                if ($categoryId === 'other') {
                    $query->whereNull('category_id');
                    return;
                }

                $query->where('category_id', $categoryId);
            })
            ->orderBy('name')
            ->orderBy('id')
            ->cursorPaginate($filters['per_page'] ?? 20)
            ->withQueryString()
            ->through(fn(Product $product) => [
                'original_price' => (int) $product->selling_price,
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'discount' => (int) ($product->discount ?? 0),
                'price' => (int) round($product->selling_price * (100 - min(100, max(0, (int) ($product->discount ?? 0)))) / 100),
                'stock' => (int) ($product->stock ?? 0),
                'category_id' => $product->category_id,
                'category' => $product->category?->name ?? 'Lainnya',
                'image' => $product->images->first()?->thumbnail_path
                    ?? $product->images->first()?->webp_path,
            ]);

        return Inertia::render('Cashier/Index', [
            'products' => $products,
            'categories' => ProductCategory::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->orderBy('id')
                ->cursorPaginate(20, ['id', 'name'], 'category_cursor')
                ->withQueryString()
                ->through(fn(ProductCategory $category) => [
                    'id' => $category->id,
                    'name' => $category->name,
                ]),
            'branches' => $branches,
            'activeBranchId' => $branchId,
            'taxConfiguration' => $taxConfiguration ? [
                'name' => $taxConfiguration->nama,
                'rate' => (float) $taxConfiguration->persentase,
                'mode' => $taxConfiguration->mode,
            ] : null,
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', SalesTransaction::class);
        return Inertia::render('Sales/Index', ['activeTab' => 'transactions', 'salesOptions' => ['branches' => $this->repository->branches((string) $this->companyContext->id())], 'transactionCreate' => true]);
    }
    public function store(StoreSalesTransactionRequest $request): RedirectResponse
    {
        Gate::authorize('create', SalesTransaction::class);
        $transaction = $this->service->create((string) $this->companyContext->id(), (string) $request->user()->id, $request->validated());

        // Load relations for invoice PDF
        $transaction->load(['branch', 'customer', 'details.product', 'creator', 'payment']);

        // Get company from context
        $company = $this->companyContext->current();

        return back()
            ->with('success', 'Transaksi penjualan berhasil disimpan.')
            ->with('invoiceData', [
                'transaction' => [
                    'id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                    'transaction_date' => $transaction->transaction_date?->format('Y-m-d'),
                    'customer' => $transaction->customer?->name,
                    'customer_detail' => $transaction->customer ? [
                        'name' => $transaction->customer->name,
                        'address' => $transaction->customer->address,
                        'email' => $transaction->customer->email,
                        'phone' => $transaction->customer->telp,
                    ] : null,
                    'delivery_method' => $transaction->delivery_method,
                    'shipping_recipient_name' => $transaction->shipping_recipient_name,
                    'shipping_phone' => $transaction->shipping_phone,
                    'shipping_address' => $transaction->shipping_address,
                    'shipping_cost' => $transaction->shipping_cost,
                    'discount' => $transaction->discount,
                    'tax' => $transaction->tax,
                    'total' => $transaction->total,
                    'note' => $transaction->note,
                    'payment_method' => $transaction->payment?->payment_method,
                    'payment_amount' => $transaction->payment?->amount,
                    'payment_status' => $transaction->payment_status,
                    'cashier_name' => $transaction->creator?->name,
                    'details' => $transaction->details->map(fn($d) => [
                        'name' => $d->product?->name ?? $d->name,
                        'quantity' => $d->quantity,
                        'unit_price' => $d->unit_price,
                        'subtotal' => $d->subtotal,
                    ])->values(),
                ],
                'branch' => $transaction->branch ? [
                    'name' => $transaction->branch->name,
                    'address' => $transaction->branch->address,
                    'city' => $transaction->branch->city,
                    'province' => $transaction->branch->province,
                    'postal_code' => $transaction->branch->postal_code,
                    'phone' => $transaction->branch->phone,
                    'email' => $transaction->branch->email,
                ] : null,
                'company' => $company ? [
                    'name' => $company->name,
                ] : null,
            ]);
    }
    public function show(SalesTransaction $transaction): JsonResponse
    {
        Gate::authorize('view', $transaction);
        $transaction = $this->repository->findWithDetails((string) $this->companyContext->id(), (string) $transaction->getKey());

        return SalesTransactionResource::make($transaction)->response();
    }

    public function edit(SalesTransaction $transaction): JsonResponse
    {
        return $this->show($transaction);
    }
    public function update(UpdateSalesTransactionRequest $request, SalesTransaction $transaction): RedirectResponse
    {
        Gate::authorize('update', $transaction);
        $this->service->update($transaction, $request->validated());
        return back()->with('success', 'Transaksi penjualan berhasil diperbarui.');
    }
    public function destroy(SalesTransaction $transaction): RedirectResponse
    {
        Gate::authorize('delete', $transaction);
        $this->service->delete($transaction->load('details'));
        return back()->with('success', 'Transaksi penjualan dibatalkan.');
    }
    public function payment(StoreSalesPaymentRequest $request, SalesTransaction $transaction): RedirectResponse
    {
        Gate::authorize('payment', $transaction);
        $this->service->addPayment($transaction, (string) $request->user()->id, $request->validated());
        return back()->with('success', 'Pembayaran berhasil ditambahkan.');
    }
    public function return(StoreSalesReturnRequest $request, SalesTransaction $transaction): RedirectResponse
    {
        Gate::authorize('manageReturn', $transaction);
        $this->service->createReturn($transaction, (string) $request->user()->id, $request->validated());
        return back()->with('success', 'Retur berhasil disimpan.');
    }

    public function complete(SalesTransaction $transaction): JsonResponse
    {
        Gate::authorize('update', $transaction);

        if ($transaction->payment_status !== 'paid') {
            return response()->json(['message' => 'Pembayaran belum lunas.'], 422);
        }

        $this->service->completeDelivery($transaction);

        return response()->json(['message' => 'Transaksi pengiriman berhasil diselesaikan.']);
    }

    public function completeReturn(StoreSalesReturnRequest $request, SalesReturn $return): RedirectResponse
    {
        Gate::authorize('manageReturn', $return->transaction);
        $this->service->completeReturn($return, $request->validated());
        return back()->with('success', 'Retur berhasil diselesaikan.');
    }
    public function destroyReturn(SalesReturn $return): RedirectResponse
    {
        Gate::authorize('manageReturn', $return->transaction);
        abort_if($return->status !== 'draft', 422, 'Retur yang sudah selesai tidak dapat dihapus.');
        $return->delete();
        return back()->with('success', 'Draft retur berhasil dihapus.');
    }
}
