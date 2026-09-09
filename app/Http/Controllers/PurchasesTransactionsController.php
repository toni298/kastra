<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purchases\IndexPurchaseTransactionRequest;
use App\Http\Requests\Purchases\StorePurchaseTransactionRequest;
use App\Http\Resources\PurchaseTransactionResource;
use App\Http\Requests\Purchases\StorePurchasePaymentRequest;
use App\Http\Requests\Purchases\StorePurchaseReturnRequest;
use App\Models\CashBankAccount;
use App\Models\Branch;
use App\Models\Gudang;
use App\Models\PurchaseTransaction;
use App\Repositories\PurchaseTransactionRepository;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;
use App\Services\PurchaseTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PurchasesTransactionsController extends Controller
{
    public function __construct(private PurchaseTransactionRepository $repository, private PurchaseTransactionService $service, private CompanyContext $companyContext, private InertiaAuthorizationService $authorization) {}
    public function index(IndexPurchaseTransactionRequest $request): Response
    {
        $this->authorization->authorize($request->user(), 'pembelian.view');
        $companyId = (string) $this->companyContext->id();
        $company = $this->companyContext->current();
        return Inertia::render('Purchases/Index', ['activeTab' => 'transactions', 'cashierLayout' => request()->routeIs('cashier.purchases'), 'purchaseItems' => PurchaseTransactionResource::collection($this->repository->paginate($companyId, $request->validated())), 'purchaseFilters' => $request->validated(), 'purchaseOptions' => ['suppliers' => $this->repository->suppliers($companyId), 'warehouses' => Gudang::query()->where('company_id', $companyId)->where('aktif', true)->orderBy('nama')->get(['id', 'nama']), 'branches' => Branch::query()->where('company_id', $companyId)->where('status', Branch::STATUS_ACTIVE)->orderBy('name')->get(['id', 'name']), 'organizationMode' => $company?->organization_mode ?? 'branch'], 'purchaseSummary' => $this->repository->summaryStats($companyId), 'capabilities' => ['create' => $this->authorization->allows($request->user(), 'pembelian.create'), 'edit' => $this->authorization->allows($request->user(), 'pembelian.edit'), 'delete' => $this->authorization->allows($request->user(), 'pembelian.delete'), 'return' => $this->authorization->allows($request->user(), 'pembelian.return'), 'print' => $this->authorization->allows($request->user(), 'pembelian.print'), 'complete' => $this->authorization->allows($request->user(), 'pembelian.complete'), 'pay' => $this->authorization->allows($request->user(), 'pembelian.pay')]]);
    }
    public function create(IndexPurchaseTransactionRequest $request): Response
    {
        Gate::authorize('pembelian.create');
        return $this->index($request);
    }
    public function store(StorePurchaseTransactionRequest $request): RedirectResponse
    {
        Gate::authorize('pembelian.create');
        $this->service->save((string)$this->companyContext->id(), (string)$request->user()->id, $request->validated(), proofFile: $request->file('proof_file'), organizationMode: $this->companyContext->current()?->organization_mode ?? 'branch');
        return back();
    }
    public function show(PurchaseTransaction $transaction): JsonResponse
    {
        $this->authorization->authorize(request()->user(), 'pembelian.view');
        return PurchaseTransactionResource::make($this->repository->findWithDetails((string) $this->companyContext->id(), (string) $transaction->getKey()))->response();
    }
    public function edit(PurchaseTransaction $transaction): JsonResponse
    {
        return $this->show($transaction);
    }
    public function update(StorePurchaseTransactionRequest $request, PurchaseTransaction $transaction): RedirectResponse
    {
        Gate::authorize('pembelian.edit');
        $this->service->save((string)$this->companyContext->id(), (string)$request->user()->id, $request->validated(), $transaction, $this->companyContext->current()?->organization_mode ?? 'branch', proofFile: $request->file('proof_file'));
        return back();
    }
    public function destroy(PurchaseTransaction $transaction): RedirectResponse
    {
        Gate::authorize('pembelian.delete');
        $this->service->delete($transaction);
        return back()->with('success', 'Transaksi pembelian berhasil dihapus.');
    }
    public function payment(StorePurchasePaymentRequest $request, PurchaseTransaction $transaction): RedirectResponse
    {
        Gate::authorize('pembelian.pay');
        $data = $request->validated();
        $data['proof_file'] = $request->file('proof_file');
        $this->service->addPayment($transaction, (string)$request->user()->id, $data);
        return back()->with('success', 'Pembayaran supplier berhasil disimpan.');
    }
    public function complete(PurchaseTransaction $transaction): RedirectResponse
    {
        Gate::authorize('pembelian.complete');
        $this->service->complete($transaction);
        return back()->with('success', 'Transaksi pembelian berhasil diselesaikan.');
    }
    public function return(StorePurchaseReturnRequest $request, PurchaseTransaction $transaction): RedirectResponse
    {
        Gate::authorize('pembelian.return');
        $this->service->createReturn($transaction, (string)$request->user()->id, $request->validated(), $this->companyContext->current()?->organization_mode ?? 'branch');
        return back()->with('success', 'Retur pembelian berhasil disimpan.');
    }

    public function refundAccounts(PurchaseTransaction $transaction): JsonResponse
    {
        Gate::authorize('pembelian.return');
        $companyId = (string) $transaction->company_id;

        $accounts = CashBankAccount::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where(function ($query) use ($companyId): void {
                $query->where('type', 'cash')
                    ->orWhereHas('settings', function ($settings) use ($companyId): void {
                        $settings->where('company_id', $companyId)
                            ->where('is_active', true)
                            ->where('is_default_receive', true);
                    });
            })
            ->select('id', 'name', 'bank_name', 'type', 'current_balance')
            ->get();

        return response()->json([
            'accounts' => $accounts->map(fn($account) => [
                'id' => $account->id,
                'label' => collect([$account->name, $account->bank_name])->filter()->join(' - '),
                'type' => $account->type,
                'balance' => $account->current_balance,
            ]),
            'multiple' => $accounts->count() > 1,
        ]);
    }
}
