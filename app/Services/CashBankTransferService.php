<?php

namespace App\Services;

use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;
use App\Models\CashBankTransfer;
use Illuminate\Support\Facades\DB;

class CashBankTransferService
{
    public function create(string $companyId, string $userId, array $data): CashBankTransfer
    {
        return DB::transaction(function () use ($companyId, $userId, $data): CashBankTransfer {
            $accounts = CashBankAccount::query()
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->whereIn('id', [$data['source_account_id'], $data['destination_account_id']])
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $source = $accounts->get($data['source_account_id']);
            $destination = $accounts->get($data['destination_account_id']);
            abort_unless($source && $destination, 422, 'Rekening transfer tidak valid.');
            abort_if($source->current_balance < $data['amount'], 422, 'Saldo rekening asal tidak mencukupi.');

            $source->decrement('current_balance', $data['amount']);
            $destination->increment('current_balance', $data['amount']);

            $transfer = CashBankTransfer::create([
                ...$data,
                'company_id' => $companyId,
                'created_by' => $userId,
                'transfer_number' => 'TRF-'.now()->format('YmdHis').'-'.strtoupper(str()->random(4)),
            ]);

            $this->recordTransferHistory($transfer, $source, $destination, $userId);

            return $transfer;
        });
    }

    /**
     * Catat history transfer ke cash_bank_transactions untuk rekening asal & tujuan.
     *
     * - Rekening asal: type "none", category "transfer antar bank" (keluar)
     * - Rekening tujuan: type "none", category "transfer antar bank" (masuk)
     * - reference di-link ke id transfer untuk traceability
     */
    private function recordTransferHistory(CashBankTransfer $transfer, CashBankAccount $source, CashBankAccount $destination, string $userId): void
    {
        $base = [
            'company_id' => $transfer->company_id,
            'created_by' => $userId,
            'type' => 'none',
            'category' => 'transfer antar bank',
            'amount' => $transfer->amount,
            'transaction_date' => $transfer->transfer_date,
            'reference' => $transfer->id,
            'status' => 'completed',
        ];

        CashBankTransaction::create([
            ...$base,
            'cash_bank_account_id' => $source->id,
            'transaction_number' => 'TRX-TRF-OUT-'.$transfer->transfer_number,
            'note' => $transfer->note ?? 'Transfer keluar ke '.$destination->name,
        ]);

        CashBankTransaction::create([
            ...$base,
            'cash_bank_account_id' => $destination->id,
            'transaction_number' => 'TRX-TRF-IN-'.$transfer->transfer_number,
            'note' => $transfer->note ?? 'Transfer masuk dari '.$source->name,
        ]);
    }
}
