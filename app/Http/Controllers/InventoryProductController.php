<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\StoreProductStockRequest;
use App\Http\Requests\Inventory\UpdateProductStockRequest;
use App\Models\ProductStock;
use App\Services\CompanyContext;
use App\Services\InventoryProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InventoryProductController extends Controller
{
    public function __construct(
        private InventoryProductService $service,
        private CompanyContext $companyContext,
    ) {}

    public function store(StoreProductStockRequest $request): RedirectResponse
    {
        Gate::authorize('create', ProductStock::class);
        $this->service->create(
            (string) $this->companyContext->id(),
            $request->validated(),
        );

        return back()->with('success', 'Produk dan stok gudang berhasil ditambahkan.');
    }

    public function update(UpdateProductStockRequest $request, ProductStock $productStock): RedirectResponse
    {
        Gate::authorize('update', $productStock);
        $this->service->update($productStock, $request->validated());

        return back()->with('success', 'Data stok produk berhasil diperbarui.');
    }

    public function destroy(Request $request, ProductStock $productStock): RedirectResponse
    {
        Gate::authorize('delete', $productStock);
        $this->service->delete($productStock);

        return back()->with('success', 'Produk berhasil dihapus dari gudang.');
    }
}
