<?php

namespace App\Services;

use App\Models\CashBankAccount;
use App\Models\CashBankAccountSetting;
use App\Models\CashBankTransaction;
use App\Models\BranchProductStock;
use App\Models\PurchaseTransaction;
use App\Models\PurchasePayment;
use App\Models\PurchaseReturn;
use App\Models\ProductStock;
use App\Services\NumberGeneratorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseTransactionService
{
    public function __construct(private NumberGeneratorService $numbers) {}

    public function save(string $companyId, string $userId, array $data, ?PurchaseTransaction $transaction = null, string $organizationMode = 'branch', $proofFile = null): PurchaseTransaction
    {
        return DB::transaction(function () use ($companyId, $userId, $data, $transaction, $organizationMode, $proofFile) {
            $rows = collect($data['details'])->map(fn($item) => ['product_id' => $item['product_id'], 'quantity' => $item['quantity'], 'unit_price' => $item['unit_price'], 'discount' => $item['discount'] ?? 0, 'subtotal' => max(0, ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0))]);
            $total = max(0, $rows->sum('subtotal') - ($data['discount'] ?? 0) + ($data['tax'] ?? 0));
            $transaction ??= new PurchaseTransaction();
            $isPaid = $data['status'] !== 'draft' && ((int) ($data['payment_amount'] ?? 0) + (int) ($data['owner_amount'] ?? 0)) >= $total;
            $transaction->fill(['company_id' => $companyId, 'supplier_id' => $data['supplier_id'] ?? null, 'gudang_id' => $data['gudang_id'] ?? null, 'created_by' => $transaction->created_by ?? $userId, 'transaction_number' => $transaction->transaction_number ?? $this->number($companyId), 'document_type' => $data['document_type'], 'status' => $data['status'], 'payment_status' => 'unpaid', 'transaction_date' => $data['transaction_date'], 'due_date' => $isPaid ? null : ($data['due_date'] ?? $transaction->due_date), 'discount' => $data['discount'] ?? 0, 'tax' => $data['tax'] ?? 0, 'total' => $total, 'note' => $data['note'] ?? null]);
            $transaction->save();
            $transaction->details()->delete();
            $transaction->details()->createMany($rows->all());

            if ($data['status'] !== 'draft') {
                $this->updateStock($companyId, $userId, $data, $rows, $organizationMode, $transaction);
            }

            if ($data['status'] !== 'draft' && ((int) ($data['payment_amount'] ?? 0) > 0 || (int) ($data['owner_amount'] ?? 0) > 0)) {
                $companyAmount = (int) ($data['payment_amount'] ?? 0);
                $ownerAmount = (int) ($data['owner_amount'] ?? 0);
                if ($companyAmount > 0 && empty($data['payment_method'])) throw ValidationException::withMessages(['payment_method' => 'Pilih metode pembayaran terlebih dahulu.']);
                $this->addPayment($transaction, $userId, ['method' => $data['payment_method'] ?? null, 'amount' => $companyAmount, 'owner_amount' => $ownerAmount, 'payment_date' => $data['transaction_date'], 'branch_id' => $data['branch_id'] ?? null, 'proof_file' => $proofFile]);
            }
            return $transaction->load(['supplier', 'gudang', 'details.product.unit']);
        });
    }

    private function updateStock(string $companyId, string $userId, array $data, $rows, string $organizationMode, PurchaseTransaction $transaction): void
    {
        $referenceType = PurchaseTransaction::class;
        $referenceId = $transaction->id;
        $note = 'Pembelian ' . $transaction->transaction_number;

        if ($organizationMode === 'branch' && !empty($data['branch_id'])) {
            foreach ($rows as $row) {
                $stock = BranchProductStock::query()
                    ->where('company_id', $companyId)
                    ->where('branch_id', $data['branch_id'])
                    ->where('product_id', $row['product_id'])
                    ->lockForUpdate()
                    ->firstOrCreate(
                        ['company_id' => $companyId, 'branch_id' => $data['branch_id'], 'product_id' => $row['product_id']],
                        ['quantity' => 0]
                    );
                app(StockMovementService::class)->move([
                    'company_id' => $companyId,
                    'branch_id' => $data['branch_id'],
                    'product_id' => $row['product_id'],
                    'user_id' => $userId,
                    'reference_number' => $transaction->transaction_number,
                    'type' => 'IN',
                    'movement_type' => 'PURCHASE',
                    'qty' => $row['quantity'],
                    'notes' => $note,
                ]);
            }
            return;
        }

        if (!empty($data['gudang_id'])) {
            foreach ($rows as $row) {
                $stock = ProductStock::query()
                    ->where('company_id', $companyId)
                    ->where('gudang_id', $data['gudang_id'])
                    ->where('product_id', $row['product_id'])
                    ->lockForUpdate()
                    ->firstOrCreate(
                        ['company_id' => $companyId, 'gudang_id' => $data['gudang_id'], 'product_id' => $row['product_id']],
                        ['quantity' => 0]
                    );
                app(StockMovementService::class)->move([
                    'company_id' => $companyId,
                    'gudang_id' => $data['gudang_id'],
                    'product_id' => $row['product_id'],
                    'user_id' => $userId,
                    'reference_number' => $transaction->transaction_number,
                    'type' => 'IN',
                    'movement_type' => 'PURCHASE',
                    'qty' => $row['quantity'],
                    'notes' => $note,
                ]);
            }
        }
    }

    public function delete(PurchaseTransaction $transaction): void
    {
        DB::transaction(fn() => $transaction->delete());
    }

    public function complete(PurchaseTransaction $transaction): PurchaseTransaction
    {
        return DB::transaction(function () use ($transaction) {
            $transaction->update(['status' => 'closed']);
            return $transaction->refresh();
        });
    }

    public function createReturn(PurchaseTransaction $transaction, string $userId, array $data, string $organizationMode = 'branch'): PurchaseReturn
    {
        return DB::transaction(function () use ($transaction, $userId, $data, $organizationMode) {
            $transaction->load(['details', 'returns.details']);
            $returned = $transaction->returns->flatMap->details->groupBy('product_id')->map(fn($rows) => $rows->sum('quantity'));
            $rows = collect($data['items'])->map(function ($item) use ($transaction, $returned) {
                $detail = $transaction->details->firstWhere('product_id', $item['product_id']);
                $max = (int)($detail?->quantity ?? 0) - (int)$returned->get($item['product_id'], 0);
                if (!$detail || (int)$item['quantity'] > $max) throw ValidationException::withMessages(['items' => 'Qty retur melebihi qty pembelian yang tersedia.']);
                return ['product_id' => $detail->product_id, 'quantity' => $item['quantity'], 'unit_price' => $detail->unit_price, 'subtotal' => $item['quantity'] * $detail->unit_price];
            });
            $returnTotal = $rows->sum('subtotal');
            $return = PurchaseReturn::create(['company_id' => $transaction->company_id, 'purchase_transaction_id' => $transaction->id, 'supplier_id' => $transaction->supplier_id, 'gudang_id' => $transaction->gudang_id, 'created_by' => $userId, 'return_number' => $this->numbers->nextForCompany($transaction->company_id, 'purchase_return', 'PRET'), 'reason' => $data['reason'], 'resolution' => $data['resolution'], 'return_date' => $data['returnDate'], 'total' => $returnTotal, 'note' => $data['note'] ?? null]);
            $return->details()->createMany($rows->all());
            foreach ($rows as $row) {
                if ($organizationMode === 'branch') {
                    $stock = BranchProductStock::query()
                        ->where('company_id', $transaction->company_id)
                        ->where('product_id', $row['product_id'])
                        ->when(!empty($data['branch_id']), fn($q) => $q->where('branch_id', $data['branch_id']))
                        ->lockForUpdate()
                        ->first();
                    if (!$stock || $stock->quantity < $row['quantity']) throw ValidationException::withMessages(['items' => 'Stok cabang tidak mencukupi untuk retur.']);
                    app(StockMovementService::class)->move([
                        'company_id' => $transaction->company_id,
                        'branch_id' => $stock->branch_id,
                        'product_id' => $row['product_id'],
                        'user_id' => $userId,
                        'reference_number' => $return->return_number,
                        'type' => 'OUT',
                        'movement_type' => 'RETUR_SUPPLIER',
                        'qty' => $row['quantity'],
                        'notes' => 'Retur pembelian ' . $return->return_number,
                    ]);
                } else {
                    $stock = ProductStock::query()->where('company_id', $transaction->company_id)->where('gudang_id', $transaction->gudang_id)->where('product_id', $row['product_id'])->lockForUpdate()->first();
                    if (!$stock || $stock->quantity < $row['quantity']) throw ValidationException::withMessages(['items' => 'Stok gudang tidak mencukupi untuk retur.']);
                    app(StockMovementService::class)->move([
                        'company_id' => $transaction->company_id,
                        'gudang_id' => $transaction->gudang_id,
                        'product_id' => $row['product_id'],
                        'user_id' => $userId,
                        'reference_number' => $return->return_number,
                        'type' => 'OUT',
                        'movement_type' => 'RETUR_SUPPLIER',
                        'qty' => $row['quantity'],
                        'notes' => 'Retur pembelian ' . $return->return_number,
                    ]);
                }
            }
            $this->processReturnResolution($return, $transaction, $userId, $returnTotal, $data['refund_account_id'] ?? null);
            $transaction->update(['status' => 'returned']);
            return $return;
        });
    }

    public function addPayment(PurchaseTransaction $transaction, string $userId, array $data): PurchasePayment
    {
        return DB::transaction(function () use ($transaction, $userId, $data) {
            $companyAmount = (int) ($data['amount'] ?? 0);
            $ownerAmount = (int) ($data['owner_amount'] ?? 0);
            $totalPayment = $companyAmount + $ownerAmount;
            $paid = (int) $transaction->payments()->sum('amount');
            $payment = $transaction->payments()->create(['company_id' => $transaction->company_id, 'created_by' => $userId, 'user_id' => $userId, 'payment_number' => $this->numbers->nextForCompany($transaction->company_id, 'purchase_payment', 'PPAY'), 'payment_method' => $data['method'], 'amount' => $totalPayment, 'payment_date' => $data['payment_date'], 'reference' => $data['reference'] ?? null, 'note' => $data['note'] ?? null]);
            $transaction->update(['payment_status' => ($paid + $totalPayment) >= (int) $transaction->total ? 'paid' : 'partial']);
            $proofFilePath = $this->storeProofFile($data['proof_file'] ?? null, $transaction->company_id);
            if ($companyAmount > 0) {
                $this->recordPaymentCashOut($transaction, $userId, $companyAmount, $payment->payment_number, $data['method'], $data['branch_id'] ?? null, $proofFilePath);
            }
            if ($ownerAmount > 0) {
                $this->recordOwnerContribution($transaction, $userId, $ownerAmount, $payment->payment_number, $proofFilePath);
            }
            return $payment;
        });
    }

    private function storeProofFile($file, string $companyId): ?string
    {
        if (!$file instanceof \Illuminate\Http\UploadedFile) {
            return null;
        }

        $directory = "purchase-payments/{$companyId}/proofs";
        return $file->store($directory, 'public');
    }

    private function processReturnResolution(PurchaseReturn $return, PurchaseTransaction $transaction, string $userId, int $returnTotal, ?string $refundAccountId = null): void
    {
        if ($return->resolution === 'refund') {
            $accountId = $this->resolveRefundAccount($transaction, $refundAccountId);
            $this->recordRefundCashOut($return, $transaction, $userId, $returnTotal, $accountId);
        } elseif ($return->resolution === 'potong_tagihan') {
            $this->applyCreditToTransaction($transaction, $returnTotal);
        }
    }

    private function applyCreditToTransaction(PurchaseTransaction $transaction, int $amount): void
    {
        $paid = (int) $transaction->payments()->sum('amount');
        $newPaid = min($paid + $amount, (int) $transaction->total);
        $transaction->update(['payment_status' => $newPaid >= (int) $transaction->total ? 'paid' : 'partial']);
    }

    private function recordPaymentCashOut(PurchaseTransaction $transaction, string $userId, int $amount, string $paymentNumber, string $paymentMethod, ?string $branchId = null, ?string $proofFilePath = null): void
    {
        $accountId = $this->resolvePaymentAccount($transaction->company_id, $branchId, $paymentMethod, outgoing: true);

        if ($accountId) {
            CashBankAccount::where('id', $accountId)->lockForUpdate()->decrement('current_balance', $amount);
        }

        CashBankTransaction::create([
            'company_id' => $transaction->company_id,
            'cash_bank_account_id' => $accountId,
            'created_by' => $userId,
            'transaction_number' => $this->cashBankNumber($transaction->company_id),
            'type' => 'out',
            'category' => 'Pembayaran Pembelian',
            'amount' => $amount,
            'transaction_date' => now()->toDateString(),
            'reference' => $transaction->id,
            'note' => 'Pembayaran ' . $paymentNumber . ' untuk ' . $transaction->transaction_number,
            'status' => 'completed',
            'proof_file_path' => $proofFilePath,
        ]);
    }

    private function recordOwnerContribution(PurchaseTransaction $transaction, string $userId, int $amount, string $paymentNumber, ?string $proofFilePath = null): void
    {
        CashBankTransaction::create([
            'company_id' => $transaction->company_id,
            'cash_bank_account_id' => null,
            'created_by' => $userId,
            'transaction_number' => $this->cashBankNumber($transaction->company_id),
            'type' => 'out',
            'category' => 'Pembayaran Pembelian dengan Dana Lain',
            'amount' => $amount,
            'transaction_date' => now()->toDateString(),
            'reference' => $transaction->id,
            'note' => 'Pembayaran dengan dana lain untuk ' . $paymentNumber . ' pada ' . $transaction->transaction_number,
            'status' => 'completed',
            'proof_file_path' => $proofFilePath,
        ]);
    }

    private function resolveRefundAccount(PurchaseTransaction $transaction, ?string $selectedAccountId): ?string
    {
        $companyId = (string) $transaction->company_id;

        $eligible = CashBankAccount::query()
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->where(function ($query) use ($companyId): void {
                $query->where('type', 'cash')
                    ->orWhereHas('settings', function ($settings) use ($companyId): void {
                        $settings->where('company_id', $companyId)
                            ->where('is_active', true)
                            ->where('is_default_receive', true);
                    });
            })
            ->select('id', 'type')
            ->lockForUpdate()
            ->get();

        if ($selectedAccountId) {
            $account = $eligible->firstWhere('id', $selectedAccountId);
            if (!$account) throw ValidationException::withMessages(['refund_account_id' => 'Rekening yang dipilih tidak valid untuk perusahaan ini.']);
            return $account->id;
        }

        return $eligible->first()?->id;
    }

    private function recordRefundCashOut(PurchaseReturn $return, PurchaseTransaction $transaction, string $userId, int $amount, ?string $accountId): void
    {
        if ($accountId) {
            CashBankAccount::where('id', $accountId)->lockForUpdate()->decrement('current_balance', $amount);
        }

        CashBankTransaction::create([
            'company_id' => $transaction->company_id,
            'cash_bank_account_id' => $accountId,
            'created_by' => $userId,
            'transaction_number' => $this->cashBankNumber($transaction->company_id),
            'type' => 'out',
            'category' => 'Retur Pembelian',
            'amount' => $amount,
            'transaction_date' => now()->toDateString(),
            'reference' => $return->id,
            'note' => 'Pengembalian dana retur pembelian ' . $return->return_number,
            'status' => 'completed',
        ]);
    }

    private function resolvePaymentAccount(string $companyId, ?string $branchId, string $paymentMethod, bool $outgoing): ?string
    {
        $accountType = match ($paymentMethod) {
            'tunai' => 'cash',
            'transfer_bank', 'giro' => 'bank',
            default => 'bank',
        };

        $flag = $outgoing ? 'can_send_money' : 'can_receive_money';
        $defaultCol = $outgoing ? 'is_default_payment' : 'is_default_receive';

        $priority = CashBankAccountSetting::query()
            ->join('cash_bank_accounts', 'cash_bank_accounts.id', '=', 'cash_bank_account_settings.cash_bank_account_id')
            ->where('cash_bank_account_settings.company_id', $companyId)
            ->where('cash_bank_account_settings.is_active', true)
            ->where('cash_bank_account_settings.' . $flag, true)
            ->where('cash_bank_account_settings.' . $defaultCol, true)
            ->where('cash_bank_accounts.is_active', true)
            ->where('cash_bank_accounts.type', $accountType)
            ->where(function ($query) use ($branchId): void {
                $query->where('cash_bank_account_settings.branch_id', $branchId)
                    ->orWhere('cash_bank_account_settings.is_all_branches', true);
            })
            ->select('cash_bank_account_settings.cash_bank_account_id')
            ->orderByRaw("CASE WHEN cash_bank_account_settings.branch_id = ? THEN 0 ELSE 1 END", [$branchId])
            ->lockForUpdate()
            ->first();

        if ($priority) {
            return $priority->cash_bank_account_id;
        }

        $fallback = CashBankAccount::query()
            ->where('company_id', $companyId)
            ->where('type', $accountType)
            ->where('is_active', true)
            ->select('id')
            ->lockForUpdate()
            ->first();

        return $fallback?->id;
    }

    private function cashBankNumber(string $companyId): string
    {
        do {
            $number = 'CB-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
        } while (CashBankTransaction::query()->where('company_id', $companyId)->where('transaction_number', $number)->exists());

        return $number;
    }

    private function number(string $companyId): string
    {
        return $this->numbers->nextForCompany($companyId, 'purchase_transaction', 'PUR');
    }
}
