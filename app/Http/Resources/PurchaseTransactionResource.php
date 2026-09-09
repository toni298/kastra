<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasDetails = $this->relationLoaded('details');
        $hasPayments = $this->relationLoaded('payments');
        $paid = $hasPayments ? (int) $this->payments->sum('amount') : 0;
        $total = (int) $this->total;
        return [
            'id' => $this->id,
            'number' => $this->transaction_number,
            'supplier' => $this->supplier?->name ?? 'Pembelian Umum',
            'supplier_id' => $this->supplier_id,
            'gudang_id' => $this->gudang_id,
            'supplierPhone' => $this->when($this->relationLoaded('supplier'), fn() => $this->supplier?->contact_supplier),
            'supplierEmail' => $this->when($this->relationLoaded('supplier'), fn() => $this->supplier?->email),
            'supplierAddress' => $this->when($this->relationLoaded('supplier'), fn() => $this->supplier?->address),
            'warehouse' => $this->gudang?->nama,
            'type' => $this->document_type === 'purchase_order' ? 'Purchase Order' : 'Purchase Invoice',
            'typeShort' => $this->document_type === 'purchase_order' ? 'PO' : 'Invoice',
            'typeVariant' => 'info',
            'status' => $this->status,
            'statusVariant' => $this->status === 'returned' ? 'warning' : ($this->status === 'completed' ? 'success' : 'info'),
            'payment' => $this->payment_status,
            'paymentVariant' => $this->payment_status === 'paid' ? 'success' : 'warning',
            'date' => $this->transaction_date?->format('d/m/Y'),
            'date_iso' => $this->transaction_date?->format('Y-m-d'),
            'due_date' => $this->due_date?->format('d/m/Y'),
            'due_date_iso' => $this->due_date?->format('Y-m-d'),
            'due_status' => $this->dueStatus(),
            'createdBy' => $this->creator?->name ?? '-',
            'total' => $total,
            'totalValue' => $total,
            'total_qty' => $hasDetails
                ? (int) $this->details->sum('quantity')
                : (int) ($this->details_sum_quantity ?? 0),
            'details_count' => (int) ($this->details_count ?? 0),
            'paidValue' => $this->when($hasPayments, $paid),
            'remainingValue' => $this->when($hasPayments, max(0, $total - $paid)),
            'payments' => $this->when($hasPayments, fn() => $this->payments->map(fn($p) => ['number' => $p->payment_number, 'method' => $p->payment_method, 'amount' => $p->amount, 'date' => $p->payment_date?->format('d/m/Y'), 'user' => $p->user?->name])->values()),
            'timeline' => $this->when($hasPayments, fn() => $this->payments->map(fn($p) => ['label' => 'Pembayaran ' . $p->payment_number . ' sebesar Rp ' . number_format($p->amount, 0, ',', '.'), 'time' => $p->payment_date?->format('d/m/Y')])->values()),
            'attachments' => [],
            'journal' => [],
            'items' => $this->when($hasDetails, fn() => $this->details->map(fn($d) => ['id' => $d->product_id, 'product_id' => $d->product_id, 'name' => $d->product?->name, 'qty' => $d->quantity, 'unit' => $d->product?->unit?->name, 'price' => $d->unit_price, 'discount' => $d->discount, 'subtotal' => $d->subtotal])->values()),
        ];
    }

    private function dueStatus(): ?array
    {
        if ($this->payment_status === 'paid' || ! $this->due_date) {
            return null;
        }

        $today = now()->startOfDay();
        $due = $this->due_date->startOfDay();

        if ($today > $due) {
            return ['label' => 'Jatuh Tempo', 'variant' => 'error'];
        }
        if ($today->eq($due)) {
            return ['label' => 'Jatuh Tempo Hari Ini', 'variant' => 'warning'];
        }

        $diff = $today->diffInDays($due);
        return ['label' => 'Tempo ' . $diff . ' hari', 'variant' => 'info'];
    }
}
