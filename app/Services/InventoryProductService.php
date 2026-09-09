<?php

namespace App\Services;

use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryProductService
{
    public function create(string $companyId, array $data): ProductStock
    {
        return DB::transaction(fn () => ProductStock::create([
                'company_id' => $companyId,
                'product_id' => $data['product_id'],
                'gudang_id' => $data['gudang_id'],
                'quantity' => $data['quantity'],
            ]));
    }

    public function update(ProductStock $stock, array $data): ProductStock
    {
        return DB::transaction(function () use ($stock, $data) {
            $stock->update($data);

            return $stock->refresh();
        });
    }

    public function delete(ProductStock $stock): void
    {
        if ($stock->quantity !== 0) {
            throw ValidationException::withMessages([
                'stock' => 'Saldo stok harus 0 sebelum produk dihapus dari gudang.',
            ]);
        }

        DB::transaction(fn () => $stock->delete());
    }
}
