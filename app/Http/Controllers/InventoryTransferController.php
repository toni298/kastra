<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\StoreTransferRequest;
use App\Http\Requests\Inventory\ReceiveTransferRequest;
use App\Http\Requests\Inventory\IndexTransferRequest;
use App\Http\Resources\TransferTransactionResource;
use App\Repositories\TransferTransactionRepository;
use App\Models\TransferTransaction;
use App\Services\CompanyContext;
use App\Services\TransferTransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use App\Services\InertiaAuthorizationService;
use Illuminate\Support\Facades\Gate;

class InventoryTransferController extends Controller
{
    public function __construct(private TransferTransactionService $service, private CompanyContext $companyContext, private TransferTransactionRepository $repository) {}

    public function index(IndexTransferRequest $request): \Inertia\Response
    {
        app(InertiaAuthorizationService::class)->authorize($request->user(), 'inventory.transfers.view');
        $companyId = (string) $this->companyContext->id();

        return \Inertia\Inertia::render(request()->routeIs('cashier.inventory.transfers') ? 'Cashier/Inventory' : 'Inventory/Index', [
            'activeTab' => 'transfer',
            'transferItems' => fn() => TransferTransactionResource::collection($this->repository->paginate($companyId, $request->validated())),
            'transferFilters' => $request->validated(),
            'transferOptions' => fn() => ['warehouses' => $this->repository->warehouses($companyId), 'branches' => $this->repository->branches($companyId)],
        ]);
    }

    public function store(StoreTransferRequest $request): RedirectResponse
    {
        Gate::authorize('create', TransferTransaction::class);
        $this->service->create((string) $this->companyContext->id(), (string) $request->user()->id, $request->validated());
        return back()->with('success', 'Transfer gudang berhasil dikirim.');
    }

    public function show(TransferTransaction $transfer): JsonResponse
    {
        Gate::authorize('view', $transfer);

        $transfer = $this->repository->findWithDetails(
            (string) $this->companyContext->id(),
            (string) $transfer->getKey(),
        );

        return TransferTransactionResource::make($transfer)->response();
    }

    public function receive(ReceiveTransferRequest $request, TransferTransaction $transfer): RedirectResponse
    {
        abort_unless((string) $transfer->company_id === (string) $this->companyContext->id(), 404);
        Gate::authorize('receive', $transfer);
        $this->service->receive($transfer, (string) $request->user()->id, $request->validated());

        return back()->with('success', 'Penerimaan transfer berhasil disimpan.');
    }
}
