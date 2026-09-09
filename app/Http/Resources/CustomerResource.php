<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $hasTransactions = $this->relationLoaded('salesTransactions');
        $transactions = $hasTransactions ? $this->salesTransactions : collect();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'telp' => $this->telp,
            'phone' => $this->telp,
            'email' => null,
            'address' => $this->address,
            'status' => $this->status,
            'variant' => $this->status === 'active' ? 'success' : 'error',
            'branch_id' => $this->branch_id,
            'branch' => $this->branch?->name,
            'transactions_count' => $hasTransactions ? $transactions->count() : (int) ($this->sales_transactions_count ?? 0),
            'last_transaction' => $this->when($hasTransactions, fn () => $transactions->first()?->transaction_date?->format('d/m/Y')),
            'sales' => $this->when($hasTransactions, fn () => 'Rp '.number_format($transactions->sum('total'), 0, ',', '.')),
            'receivable' => $this->when($hasTransactions, fn () => 'Rp '.number_format($transactions->where('payment_status', 'unpaid')->sum('total'), 0, ',', '.')),
            'active' => $this->when($hasTransactions, fn () => $transactions->where('status', 'completed')->count()),
            'timeline' => $this->when($hasTransactions, fn () => $transactions->take(5)->map(fn ($transaction) => ['title' => $transaction->transaction_number, 'date' => $transaction->transaction_date?->format('d/m/Y')])->values()),
            'documents' => $this->when($hasTransactions, fn () => $transactions->map(fn ($transaction) => ['number' => $transaction->transaction_number, 'date' => $transaction->transaction_date?->format('d/m/Y'), 'date_iso' => $transaction->transaction_date?->format('Y-m-d'), 'status' => $transaction->status, 'payment_status' => $transaction->payment_status, 'total' => 'Rp '.number_format($transaction->total, 0, ',', '.'), 'total_amount' => (int) $transaction->total])->values()),
        ];
    }
}
