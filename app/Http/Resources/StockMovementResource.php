<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
   public function toArray(Request $request): array
   {
      return [
         'id' => $this->id,
         'type' => $this->type,
         'movement_type' => self::movementTypeLabel($this->movement_type),
         'product_sku' => $this->product_sku,
         'product_name' => $this->product_name,
         'unit_name' => $this->unit_name,
         'qty' => $this->qty,
         'stock_before' => $this->stock_before,
         'stock_after' => $this->stock_after,
         'location_name' => $this->location_name,
         'reference_number' => $this->reference_number,
         'user_name' => $this->user_name,
         'notes' => $this->notes,
         'created_at' => $this->created_at?->toISOString(),
      ];
   }

   private static function movementTypeLabel(?string $movementType): string
   {
      return match (strtoupper((string) $movementType)) {
         'SALE' => 'Penjualan',
         'PURCHASE' => 'Pembelian',
         'OPNAME_ADJUSTMENT', 'ADJUSTMENT' => 'Penyesuaian Opname',
         'VOID_SALE' => 'Pembatalan Penjualan',
         'RETUR_CUSTOMER' => 'Retur Pelanggan',
         'RETUR_SUPPLIER' => 'Retur Supplier',
         'DAMAGE' => 'Barang Rusak',
         'EXPIRED' => 'Barang Kedaluwarsa',
         'TRANSFER_IN' => 'Transfer Masuk',
         'TRANSFER_OUT' => 'Transfer Keluar',
         'IN' => 'Stok Masuk',
         'OUT' => 'Stok Keluar',
         default => $movementType ?: 'Tidak Diketahui',
      };
   }
}
