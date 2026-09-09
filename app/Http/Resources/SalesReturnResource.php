<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalesReturnResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasDetails = $this->relationLoaded('details');
        $hasReplacements = $this->relationLoaded('replacements');

        return [
            'id' => $this->id,
            'return_number' => $this->return_number,
            'reference_number' => $this->transaction?->transaction_number,
            'transaction_id' => $this->sales_transaction_id,
            'customer' => $this->transaction?->customer?->name ?? 'Penjualan Umum',
            'branch' => $this->branch?->name,
            'reason' => $this->reason,
            'reason_label' => ['damaged' => 'Barang Rusak', 'wrong_delivery' => 'Salah Kirim', 'not_as_ordered' => 'Barang Tidak Sesuai', 'expired' => 'Kadaluarsa', 'other' => 'Lainnya'][$this->reason] ?? $this->reason,
            'resolution' => $this->resolution,
            'resolution_label' => ['refund' => 'Refund', 'ganti_produk' => 'Ganti Produk', 'potong_tagihan' => 'Potong Tagihan'][$this->resolution] ?? $this->resolution,
            'status' => $this->status,
            'date' => $this->created_at?->format('d/m/Y'),
            'total' => (int) $this->total,
            'refund_amount' => (int) $this->refund_amount,
            'replacement_total' => (int) $this->replacement_total,
            'customer_credit_amount' => (int) $this->customer_credit_amount,
            'customer_pays_amount' => (int) $this->customer_pays_amount,
            'details_count' => (int) ($this->details_count ?? 0),
            'details' => $this->when($hasDetails, fn() => $this->details->map(fn($detail) => ['product_id' => $detail->product_id, 'product' => $detail->product?->name, 'quantity' => $detail->quantity, 'unit' => $detail->product?->unit?->name, 'unit_price' => $detail->unit_price, 'subtotal' => $detail->subtotal])->values()),
            'replacements' => $this->when($hasReplacements, fn() => $this->replacements->map(fn($item) => ['product_id' => $item->product_id, 'product' => $item->product?->name, 'quantity' => $item->quantity, 'unit' => $item->product?->unit?->name, 'unit_price' => $item->unit_price, 'subtotal' => $item->subtotal])->values()),
        ];
    }
}
