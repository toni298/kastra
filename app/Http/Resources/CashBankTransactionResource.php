<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CashBankTransactionResource extends JsonResource
{
   public function toArray(Request $request): array
   {
      $amount = match ($this->type) {
         'in' => $this->amount,
         'out' => -$this->amount,
         default => $this->amount,
      };

      $date = $this->transaction_date?->format('d/m/Y');

      return [
         'id' => $this->id,
         'cash_bank_account_id' => $this->cash_bank_account_id,
         'type' => $this->type,
         'category' => $this->category,
         'reference' => $this->transaction_number,
         'description' => $this->category,
         'account' => $this->account?->name,
         'amount' => $amount,
         'amount_value' => $this->amount,
         'date' => $date,
         'displayDate' => $date,
         'isoDate' => $this->transaction_date?->toDateString(),
         'time' => $this->created_at?->format('H:i'),
         'status' => 'Berhasil',
         'statusVariant' => 'success',
         'transactionType' => match ($this->type) {
            'in' => 'Kas Masuk',
            'out' => 'Kas Keluar',
            default => 'Tidak Mempengaruhi Kas',
         },
         'source' => 'Kas & Bank',
         'sourceLabel' => 'Pencatatan langsung Kas & Bank',
         'party' => $this->account?->name,
         'sourceRoute' => null,
         'balanceBefore' => 0,
         'balanceAfter' => 0,
         'note' => $this->note,
         'externalReference' => $this->reference,
         'proof_file_path' => $this->proof_file_path,
         'proof_file_url' => $this->proof_file_path ? Storage::url($this->proof_file_path) : null,
         'journal' => [],
         'timeline' => [
            ['label' => 'Transaksi dicatat', 'time' => $this->created_at?->format('d/m/Y H:i')],
         ],
      ];
   }
}
