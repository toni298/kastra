<?php

namespace App\Services;

use App\Models\BranchProductStock;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Gudang;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockMovementService
{
   /**
    * Atomically changes one stock balance and appends its immutable ledger row.
    * Writes only immutable snapshot data to the ledger.
    */
   public function move(array $data): StockMovement
   {
      return DB::transaction(function () use ($data): StockMovement {
         $stock = $this->lockStock($data);
         $before = (float) $stock->quantity;
         $qty = (float) $data['qty'];
         $type = strtoupper($data['type']);
         $after = $type === 'IN' ? $before + $qty : $before - $qty;

         if ($qty <= 0 || ($type === 'OUT' && $after < 0)) {
            throw ValidationException::withMessages(['qty' => 'Jumlah pergerakan stok tidak valid.']);
         }

         $stock->update(['quantity' => $after]);
         $product = Product::query()->where('company_id', $data['company_id'])->findOrFail($data['product_id']);
         $locationName = $data['location_name'] ?? $this->locationName($data);
         $userName = $data['user_name'] ?? User::query()->whereKey($data['user_id'] ?? null)->value('name') ?? 'System';

         return StockMovement::create([
            'company_id' => $data['company_id'],
            'branch_id' => $data['branch_id'] ?? null,
            'gudang_id' => $data['gudang_id'] ?? null,
            'product_id' => $data['product_id'],
            'product_sku' => $data['product_sku'] ?? $product->sku,
            'product_name' => $data['product_name'] ?? $product->name,
            'unit_name' => $data['unit_name'] ?? Unit::query()->whereKey($product->unit_id)->value('name'),
            'location_name' => $locationName,
            'type' => $type,
            'movement_type' => strtoupper($data['movement_type']),
            'qty' => $qty,
            'stock_before' => $before,
            'stock_after' => $after,
            'reference_number' => $data['reference_number'] ?? 'MOV-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5)),
            'user_id' => $data['user_id'] ?? null,
            'user_name' => $userName,
            'notes' => $data['notes'] ?? null,
         ]);
      });
   }

   private function locationName(array $data): string
   {
      if (! empty($data['gudang_id'])) {
         return (string) (Gudang::query()->whereKey($data['gudang_id'])->value('nama') ?? 'Gudang');
      }

      return (string) (Branch::query()->whereKey($data['branch_id'] ?? null)->value('name') ?? 'Cabang');
   }

   private function lockStock(array $data): ProductStock|BranchProductStock
   {
      $query = ($data['gudang_id'] ?? null)
         ? ProductStock::query()->where('gudang_id', $data['gudang_id'])
         : BranchProductStock::query()->where('branch_id', $data['branch_id']);

      $stock = $query->where('company_id', $data['company_id'])
         ->where('product_id', $data['product_id'])
         ->lockForUpdate()
         ->first();

      if (! $stock) {
         throw ValidationException::withMessages(['product_id' => 'Stok produk tidak ditemukan pada lokasi ini.']);
      }

      return $stock;
   }
}
