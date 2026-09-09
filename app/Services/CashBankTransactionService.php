<?php

namespace App\Services;

use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CashBankTransactionService
{
    public function create(string $companyId, string $userId, array $data): CashBankTransaction
    {
        return DB::transaction(function () use ($companyId, $userId, $data): CashBankTransaction {
            $account = $this->accountFor($companyId, $data);
            $this->applyBalance($account, $data['type'], $data['amount']);

            $proofFilePath = $this->storeProofFile($data, $companyId);

            return CashBankTransaction::create([
                ...Arr::except($data, ['proof_file']),
                'company_id' => $companyId,
                'created_by' => $userId,
                'transaction_number' => 'CB-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4)),
                'status' => 'completed',
                'proof_file_path' => $proofFilePath,
            ]);
        });
    }

    public function update(CashBankTransaction $transaction, array $data): CashBankTransaction
    {
        return DB::transaction(function () use ($transaction, $data): CashBankTransaction {
            $oldAccount = $transaction->cash_bank_account_id ? CashBankAccount::query()->lockForUpdate()->find($transaction->cash_bank_account_id) : null;
            $this->applyBalance($oldAccount, $transaction->type, -$transaction->amount);
            $account = $this->accountFor((string) $transaction->company_id, $data);
            $this->applyBalance($account, $data['type'], $data['amount']);

            $proofFilePath = $transaction->proof_file_path;
            if (isset($data['proof_file']) && $data['proof_file'] instanceof UploadedFile) {
                $this->deleteProofFile($transaction->proof_file_path);
                $proofFilePath = $this->storeProofFile($data, (string) $transaction->company_id);
            } elseif (! empty($data['proof_file_removed'])) {
                $this->deleteProofFile($transaction->proof_file_path);
                $proofFilePath = null;
            }

            $transaction->update([
                ...Arr::except($data, ['proof_file']),
                'proof_file_path' => $proofFilePath,
            ]);

            return $transaction->refresh();
        });
    }

    public function delete(CashBankTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $oldAccount = $transaction->cash_bank_account_id ? CashBankAccount::query()->lockForUpdate()->find($transaction->cash_bank_account_id) : null;
            $this->applyBalance($oldAccount, $transaction->type, -$transaction->amount);
            $this->deleteProofFile($transaction->proof_file_path);
            $transaction->delete();
        });
    }

    private function storeProofFile(array $data, string $companyId): ?string
    {
        if (! isset($data['proof_file']) || ! ($data['proof_file'] instanceof UploadedFile)) {
            return null;
        }

        $file = $data['proof_file'];
        $filename = Str::uuid() . '.' . $file->extension();

        return $file->storeAs("cash-bank/{$companyId}/proofs", $filename, 'public') ?: null;
    }

    private function deleteProofFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function accountFor(string $companyId, array $data): ?CashBankAccount
    {
        if (! $data['cash_bank_account_id']) return null;

        $account = CashBankAccount::query()->where('company_id', $companyId)->where('is_active', true)->lockForUpdate()->findOrFail($data['cash_bank_account_id']);
        if ($data['type'] === 'none') return $account;

        $column = $data['type'] === 'in' ? 'can_receive_money' : 'can_send_money';
        if (! $account->settings()->where('is_active', true)->where($column, true)->exists()) {
            throw ValidationException::withMessages(['cash_bank_account_id' => 'Rekening tidak dapat digunakan untuk jenis transaksi ini.']);
        }

        return $account;
    }

    private function applyBalance(?CashBankAccount $account, string $type, int $amount): void
    {
        if (! $account || $type === 'none') return;
        $change = $type === 'in' ? $amount : -$amount;
        if ($change < 0 && $account->current_balance < abs($change)) {
            throw ValidationException::withMessages(['amount' => 'Saldo rekening tidak mencukupi.']);
        }
        $account->increment('current_balance', $change);
    }
}
