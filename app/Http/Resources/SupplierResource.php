<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'contact_supplier' => $this->contact_supplier,
            'phone' => $this->contact_supplier,
            'email' => $this->email,
            'address' => $this->address,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'purchase_transactions_count' => (int) ($this->purchase_transactions_count ?? 0),
            'purchases' => 'Rp 0',
            'active' => '0 Invoice',
            'last' => '-',
            'purchases' => $this->relationLoaded('purchaseTransactions') ? 'Rp ' . number_format((int) $this->purchaseTransactions->sum('total'), 0, ',', '.') : 'Rp 0',
            'active' => $this->relationLoaded('purchaseTransactions') ? $this->purchaseTransactions->whereIn('status', ['draft', 'ordered', 'received', 'completed'])->count() . ' Invoice' : '0 Invoice',
            'activeInvoiceValue' => $this->relationLoaded('purchaseTransactions') ? $this->purchaseTransactions->whereIn('status', ['draft', 'ordered', 'received', 'completed'])->sum('total') : 0,
            'invoices' => $this->when($this->relationLoaded('purchaseTransactions'), fn() => $this->purchaseTransactions->map(fn($transaction) => ['number' => $transaction->transaction_number, 'date' => $transaction->transaction_date?->format('d/m/Y'), 'total' => 'Rp ' . number_format($transaction->total, 0, ',', '.'), 'status' => $transaction->status, 'variant' => $transaction->status === 'completed' ? 'success' : 'warning'])->values()),
        ];
    }
}
