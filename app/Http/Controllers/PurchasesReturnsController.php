<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purchases\IndexPurchaseReturnRequest;
use App\Http\Resources\PurchaseReturnResource;
use App\Repositories\PurchaseReturnRepository;
use App\Services\CompanyContext;
use App\Services\InertiaAuthorizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PurchasesReturnsController extends Controller
{
    public function __construct(private PurchaseReturnRepository $repository, private CompanyContext $companyContext, private InertiaAuthorizationService $authorization) {}
    public function index(IndexPurchaseReturnRequest $request): Response
    {
        $this->authorization->authorize($request->user(), 'pembelian.returns.view');
        return Inertia::render('Purchases/Index', ['activeTab' => 'returns', 'cashierLayout' => request()->routeIs('cashier.purchases.*'), 'purchaseReturnItems' => PurchaseReturnResource::collection($this->repository->paginate((string)$this->companyContext->id(), $request->validated())), 'purchaseReturnFilters' => $request->validated()]);
    }
    public function create(IndexPurchaseReturnRequest $request): Response
    {
        return $this->index($request);
    }
    public function store(Request $request): RedirectResponse
    {
        return back()->with('success', 'Retur pembelian berhasil disimpan.');
    }
    public function show(string $return): JsonResponse
    {
        $this->authorization->authorize(request()->user(), 'pembelian.returns.view');
        $purchaseReturn = $this->repository->findWithDetails((string) $this->companyContext->id(), $return);
        $items = $purchaseReturn->details()->with('product.unit')->cursorPaginate(20);
        $data = PurchaseReturnResource::make($purchaseReturn)->resolve(request());
        $data['items'] = ['data' => collect($items->items())->map(fn($item) => ['id' => $item->id, 'name' => $item->product?->name, 'quantity' => $item->quantity, 'unit' => $item->product?->unit?->name, 'unitPrice' => $item->unit_price, 'subtotal' => $item->subtotal])->values(), 'next_cursor' => $items->nextCursor()?->encode()];
        return response()->json(['data' => $data]);
    }
    public function edit(string $return): JsonResponse
    {
        return $this->show($return);
    }
    public function update(Request $request, string $return): RedirectResponse
    {
        return back()->with('success', 'Retur pembelian berhasil diperbarui.');
    }
    public function destroy(string $return): RedirectResponse
    {
        return back()->with('success', 'Retur pembelian berhasil dihapus.');
    }
}
