<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockReportResource extends JsonResource
{
   public function toArray(Request $request): array
   {
      return [
         'id' => $this->product_id,
         'product_id' => $this->product_id,
         'sku' => $this->sku ?? '-',
         'name' => $this->name ?? '-',
         'category' => $this->category?->name ?? '-',
         'brand' => $this->brand?->name ?? '-',
         'unit' => $this->unit?->name ?? '-',
         'last_movement_at' => $this->last_movement_at,
         'stock_initial' => (float) $this->stock_initial,
         'total_in' => (float) $this->total_in,
         'total_out' => (float) $this->total_out,
         'adjustment' => (float) $this->adjustment,
         'stock_final' => (float) $this->stock_final,
         'valuation' => (int) $this->valuation,
         'is_low_stock' => (bool) $this->is_low_stock,
         'minimum_stock' => (float) $this->minimum_stock,
      ];
   }
}
