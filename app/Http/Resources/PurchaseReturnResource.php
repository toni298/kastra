<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseReturnResource extends JsonResource
{
   public function toArray(Request $request): array
   {
      $hasDetails = $this->relationLoaded('details'); return ['id' => $this->id, 'number' => $this->return_number, 'reference' => $this->transaction?->transaction_number, 'supplier' => $this->supplier?->name ?? 'Pembelian Umum', 'supplierPhone' => $this->when($this->relationLoaded('supplier'), fn () => $this->supplier?->contact_supplier), 'date' => $this->return_date?->format('d/m/Y'), 'reason' => $this->reason, 'resolution' => $this->resolution, 'note' => $this->note, 'status' => 'Selesai', 'statusVariant' => 'success', 'totalValue' => (int)$this->total, 'value' => 'Rp ' . number_format($this->total, 0, ',', '.'), 'createdBy' => $this->creator?->name ?? '-', 'details_count' => (int) ($this->details_count ?? 0), 'items' => $this->when($hasDetails, fn () => $this->details->map(fn($d) => ['name' => $d->product?->name, 'quantity' => $d->quantity, 'unit' => $d->product?->unit?->name, 'unitPrice' => $d->unit_price, 'subtotal' => $d->subtotal])->values()), 'timeline' => [['title' => 'Retur dibuat', 'date' => $this->created_at?->format('d/m/Y H:i')]]];
   }
}
