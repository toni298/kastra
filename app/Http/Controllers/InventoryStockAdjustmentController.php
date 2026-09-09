<?php

namespace App\Http\Controllers;

use App\Http\Requests\Inventory\StoreStockAdjustmentRequest;
use App\Models\Branch;
use App\Models\Product;
use App\Services\CompanyContext;
use App\Services\StockAdjustmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class InventoryStockAdjustmentController extends Controller
{
    public function store(
        StoreStockAdjustmentRequest $request,
        StockAdjustmentService $service,
        CompanyContext $companyContext
    ): RedirectResponse {
        $companyId = (string) $companyContext->id();
        $data = $request->validated();

        // Pastikan cabang & produk milik company yang sama.
        $branch = Branch::query()
            ->where('id', $data['branch_id'])
            ->where('company_id', $companyId)
            ->first();
        $product = Product::query()
            ->where('id', $data['product_id'])
            ->where('company_id', $companyId)
            ->first();

        if (! $branch || ! $product) {
            throw ValidationException::withMessages([
                'product_id' => 'Cabang atau produk tidak valid untuk perusahaan ini.',
            ]);
        }

        $service->adjust(
            $companyId,
            $branch->id,
            $product->id,
            $data['type'],
            (int) $data['quantity'],
            $data['note'] ?? null
        );

        $direction = $data['type'] === 'in' ? 'ditambah' : 'dikurangi';

        return back()->with('success', "Stok {$product->name} di cabang {$branch->name} berhasil {$direction} sebanyak {$data['quantity']}.");
    }
}
