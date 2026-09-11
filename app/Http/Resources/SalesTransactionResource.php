<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\BranchProductStock;

class SalesTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasDetails = $this->relationLoaded('details');
        $details = $hasDetails ? $this->details : collect();
        $subtotal = $details->sum('subtotal');
        return [
            'id' => $this->id,
            'number' => $this->transaction_number,
            'branch_id' => $this->branch_id,
            'branch' => $this->branch?->name,
            'branch_address' => $this->branch?->address,
            'branch_city' => $this->branch?->city,
            'branch_province' => $this->branch?->province,
            'branch_postal_code' => $this->branch?->postal_code,
            'branch_phone' => $this->branch?->phone,
            'branch_email' => $this->branch?->email,
            'user' => $this->creator?->name,
            'customer' => $this->customer?->name ?: 'Penjualan Umum',
            'customer_id' => $this->customer_id,
            'customer_detail' => $this->when($this->relationLoaded('customer') && $this->customer, fn() => [
                'name' => $this->customer->name,
                'phone' => $this->customer->telp,
                'telp' => $this->customer->telp,
                'address' => $this->customer->address
            ]),
            'document_type' => $this->document_type,
            'transaction_date' => $this->transaction_date,
            'date' => $this->formatDateIndonesia($this->transaction_date),
            'date_iso' => $this->transaction_date?->format('Y-m-d'),
            'due_date' => $this->formatDateIndonesia($this->due_date),
            'due_date_iso' => $this->due_date?->format('Y-m-d'),
            'due_status' => $this->dueStatus(),
            'status' => $this->status,
            'finalized_at' => $this->finalized_at?->toISOString(),
            'finalized_by' => $this->when($this->relationLoaded('finalizedBy'), fn() => $this->finalizedBy?->only('id', 'name')),
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method ?? ($this->relationLoaded('payment') ? $this->payment?->payment_method : null),
            'delivery_method' => $this->delivery_method,
            'shipping_recipient_name' => $this->shipping_recipient_name,
            'shipping_phone' => $this->shipping_phone,
            'shipping_address' => $this->shipping_address,
            'shipping_cost' => $this->shipping_cost,
            'total' => $this->total,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'details_count' => (int) ($this->details_count ?? 0),
            'total_qty' => (float) ($this->details_sum_quantity ?? 0),
            'tax_rate' => $subtotal ? (int) round($this->tax / $subtotal * 100) : 0,
            'payment' => $this->when($this->relationLoaded('payment'), fn() => $this->payment ? ['method' => $this->payment->payment_method, 'amount' => $this->payment->amount, 'status' => $this->payment->payment_status, 'reference' => $this->payment->reference_number] : null),
            'payment_history' => $this->when($this->relationLoaded('payments'), fn() => $this->payments->map(fn($payment) => ['number' => $payment->payment_number, 'method' => $payment->payment_method, 'amount' => $payment->amount, 'status' => $payment->payment_status, 'date' => $this->formatDateIndonesia($payment->payment_date)])->values()),
            'outstanding' => $this->when($this->relationLoaded('payments'), fn() => max(0, (int) $this->total - (int) $this->payments->whereIn('payment_status', ['paid', 'pending'])->sum('amount'))),
            'note' => $this->note,
            'details' => $this->when($hasDetails, fn() => $details->map(fn($detail) => [
                'id' => $detail->id,
                'product_id' => $detail->product_id,
                'product' => $detail->product?->name,
                'sku' => $detail->product?->sku,
                'unit' => $detail->product?->unit?->name,
                'quantity' => $detail->quantity,
                'unit_price' => $detail->unit_price,
                'subtotal' => $detail->subtotal,
                'available_quantity' => $this->availableQuantity($detail),
                'returned_quantity' => $this->returnedQuantity($detail),
                'returnable_quantity' => max(0, (int) $detail->quantity - $this->returnedQuantity($detail)),
            ])->values()),
        ];
    }

    private function availableQuantity($detail): int
    {
        $quantity = (int) BranchProductStock::query()
            ->where('company_id', $this->company_id)
            ->where('branch_id', $this->branch_id)
            ->where('product_id', $detail->product_id)
            ->value('quantity');

        // Completed transactions have already reduced stock. During edit,
        // the old quantity is restored before the new quantity is validated.
        return $quantity + ($this->status === 'completed' ? (int) $detail->quantity : 0);
    }

    private function returnedQuantity($detail): int
    {
        if (! $this->relationLoaded('returns')) return 0;
        return $this->returns->where('status', 'completed')->flatMap->details->where('product_id', $detail->product_id)->sum('quantity');
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

    private function formatDateIndonesia($date): ?string
    {
        if (!$date) return null;

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        $day = $date->day;
        $month = $months[$date->month];
        $year = $date->year;

        return "{$day} {$month} {$year}";
    }
}
