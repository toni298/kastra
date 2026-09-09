<?php

namespace App\Services;

use App\Models\BranchProductStock;
use App\Models\CashBankAccount;
use App\Models\CashBankAccountSetting;
use App\Models\CashBankTransaction;
use App\Models\Customer;
use App\Models\SalesTransaction;
use App\Models\SalesPayment;
use App\Models\SalesReturn;
use App\Repositories\SalesTransactionRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesTransactionService
{
    public function __construct(private SalesTransactionRepository $repository, private NumberGeneratorService $numberGenerator) {}

    public function create(string $companyId, string $userId, array $data): SalesTransaction
    {
        return DB::transaction(function () use ($companyId, $userId, $data) {
            $status = $data['status'] ?? 'completed';
            $details = $status === 'completed'
                ? $this->prepareDetails($companyId, $data['branch_id'], $data['details'])
                : collect($data['details'])->map(fn(array $item) => ['product_id' => $item['product_id'], 'quantity' => $item['quantity'], 'unit_price' => $item['unit_price'], 'subtotal' => $item['quantity'] * $item['unit_price']]);
            $total = $this->total($details, $data);
            $isPaid = (int) $data['payment_amount'] >= $total;
            $transaction = SalesTransaction::create([
                'company_id' => $companyId,
                'branch_id' => $data['branch_id'],
                'customer_id' => $data['customer_id'] ?? null,
                'created_by' => $userId,
                'transaction_number' => $this->number($companyId),
                'document_type' => $data['document_type'],
                'transaction_date' => $data['transaction_date'],
                'due_date' => $isPaid ? null : ($data['due_date'] ?? null),
                'status' => $status,
                'payment_status' => 'unpaid',
                'discount' => $data['discount'] ?? 0,
                'tax' => $data['tax'] ?? 0,
                'total' => $total,
                'note' => $data['note'] ?? null,
            ]);
            $transaction->details()->createMany($details->all());
            if ($status === 'completed') {
                $this->recordSaleStockOut($transaction, $details);
            }
            $transaction->update(['payment_status' => $isPaid ? 'paid' : 'unpaid']);
            $proofFilePath = $this->storeProofFile($data['proof_file'] ?? null, $companyId);
            $transaction->payment()->create(['company_id' => $companyId, 'branch_id' => $data['branch_id'], 'payment_number' => $this->numberGenerator->nextForCompany($companyId, 'sales_payment'), 'payment_method' => $data['payment_method'], 'amount' => $data['payment_amount'], 'payment_status' => $data['payment_amount'] >= $transaction->total ? 'paid' : 'pending', 'payment_date' => $data['transaction_date'], 'reference_number' => $data['payment_reference'] ?? null, 'note' => $data['payment_note'] ?? null, 'received_by' => $userId]);
            if ($status === 'completed') {
                $this->recordCashBankTransaction($transaction, $userId, min((int) $data['payment_amount'], (int) $transaction->total), $data['payment_method'], $proofFilePath);
            }
            return $transaction;
        });
    }

    private function storeProofFile($file, string $companyId): ?string
    {
        if (!$file instanceof \Illuminate\Http\UploadedFile) {
            return null;
        }

        $directory = "sales-payments/{$companyId}/proofs";
        return $file->store($directory, 'public');
    }

    public function delete(SalesTransaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            if ($transaction->status === 'completed') {
                $this->recordSaleStockIn($transaction, $transaction->details->map(fn($d) => ['product_id' => $d->product_id, 'quantity' => $d->quantity]), 'Pembatalan penjualan ');
            }
            $transaction->update(['status' => 'cancelled']);
        });
    }

    public function update(SalesTransaction $transaction, array $data): SalesTransaction
    {
        return DB::transaction(function () use ($transaction, $data) {
            $transaction->load('details');
            if ($transaction->status === 'completed') {
            }
            $status = $data['status'] ?? $transaction->status;
            $details = $status === 'completed'
                ? $this->prepareDetails($transaction->company_id, $data['branch_id'], $data['details'])
                : collect($data['details'])->map(fn(array $item) => ['product_id' => $item['product_id'], 'quantity' => $item['quantity'], 'unit_price' => $item['unit_price'], 'subtotal' => $item['quantity'] * $item['unit_price']]);
            $total = $this->total($details, $data);
            $isPaid = (int) $data['payment_amount'] >= $total;
            $transaction->update(['branch_id' => $data['branch_id'], 'customer_id' => $data['customer_id'] ?? null, 'document_type' => $data['document_type'], 'transaction_date' => $data['transaction_date'], 'due_date' => $isPaid ? null : ($data['due_date'] ?? $transaction->due_date), 'status' => $status, 'payment_status' => $isPaid ? 'paid' : 'unpaid', 'discount' => $data['discount'] ?? 0, 'tax' => $data['tax'] ?? 0, 'total' => $total, 'note' => $data['note'] ?? null]);
            $oldDetails = $transaction->details->map(fn($d) => ['product_id' => $d->product_id, 'quantity' => $d->quantity]);
            $transaction->details()->delete();
            $transaction->details()->createMany($details->all());
            if ($transaction->status === 'completed') {
                $this->recordSaleStockIn($transaction, collect($oldDetails), 'Pembatalan detail (edit) ');
                $this->recordSaleStockOut($transaction, $details);
            }
            $payment = $transaction->payment()->first();
            $paymentData = ['company_id' => $transaction->company_id, 'branch_id' => $data['branch_id'], 'payment_number' => $payment?->payment_number ?? $this->numberGenerator->nextForCompany($transaction->company_id, 'sales_payment'), 'payment_method' => $data['payment_method'], 'amount' => $data['payment_amount'], 'payment_status' => $data['payment_amount'] >= $transaction->total ? 'paid' : 'pending', 'payment_date' => $data['transaction_date'], 'reference_number' => $data['payment_reference'] ?? null, 'note' => $data['payment_note'] ?? null];
            if ($payment) $payment->update($paymentData);
            else $transaction->payments()->create($paymentData);
            return $transaction;
        });
    }

    public function addPayment(SalesTransaction $transaction, string $userId, array $data): SalesPayment
    {
        return DB::transaction(function () use ($transaction, $userId, $data) {
            $transaction->load('payments');
            $paid = (int) $transaction->payments->whereIn('payment_status', ['paid', 'pending'])->sum('amount');
            $outstanding = max(0, (int) $transaction->total - $paid);
            if ((int) $data['amount'] > $outstanding) throw ValidationException::withMessages(['amount' => 'Jumlah pembayaran melebihi sisa hutang.']);
            $payment = $transaction->payments()->create([
                'company_id' => $transaction->company_id,
                'branch_id' => $transaction->branch_id,
                'payment_number' => $this->numberGenerator->nextForCompany($transaction->company_id, 'sales_payment'),
                'payment_method' => $data['payment_method'],
                'amount' => $data['amount'],
                'payment_status' => (int) $data['amount'] >= $outstanding ? 'paid' : 'pending',
                'payment_date' => $data['payment_date'],
                'received_by' => $userId,
            ]);
            $transaction->update(['payment_status' => ($paid + (int) $data['amount']) >= (int) $transaction->total ? 'paid' : 'unpaid']);
            $proofFilePath = $this->storeProofFile($data['proof_file'] ?? null, $transaction->company_id);
            $this->recordPaymentCashIn($transaction, $userId, (int) $data['amount'], $payment->payment_number, $data['payment_method'], $proofFilePath);
            return $payment;
        });
    }

    public function completeDelivery(SalesTransaction $transaction): SalesTransaction
    {
        return DB::transaction(function () use ($transaction) {
            if ($transaction->status !== 'completed') {
                $transaction->update(['status' => 'completed']);
            }

            return $transaction;
        });
    }

    public function createReturn(SalesTransaction $transaction, string $userId, array $data): SalesReturn
    {
        return DB::transaction(function () use ($transaction, $userId, $data) {
            $transaction->load(['details', 'returns.details']);
            $rows = $this->validateReturnItems($transaction, $data['items']);
            $returnTotal = $rows->sum('subtotal');
            $resolution = $data['resolution'] ?? 'refund';
            $this->validateSettlement($resolution, $data, $returnTotal);
            $settlement = $this->calculateSettlement($resolution, $data, $returnTotal);
            $return = SalesReturn::create(['company_id' => $transaction->company_id, 'branch_id' => $transaction->branch_id, 'sales_transaction_id' => $transaction->id, 'created_by' => $userId, 'return_number' => $this->returnNumber($transaction->company_id), 'reason' => $data['reason'], 'resolution' => $resolution, 'status' => $data['status'], 'note' => $data['note'] ?? null, 'total' => $returnTotal, 'refund_amount' => $settlement['refund_amount'], 'replacement_total' => $settlement['replacement_total'], 'customer_credit_amount' => $settlement['customer_credit_amount'], 'customer_pays_amount' => $settlement['customer_pays_amount']]);
            $return->details()->createMany($rows->all());
            if (! empty($settlement['replacements'])) {
                $return->replacements()->createMany($settlement['replacements']);
            }
            if ($data['status'] === 'completed') {
                $this->processReturnStockIn($return, $rows);
                $this->processResolution($return, $transaction, $userId, $settlement);
                $transaction->update(['status' => $this->computeReturnStatus($transaction, $rows)]);
            } else {
                $transaction->update(['status' => $this->computeReturnStatus($transaction, collect(), true)]);
            }
            return $return;
        });
    }

    private function processReturnStockIn(SalesReturn $return, Collection $rows): void
    {
        foreach ($rows as $row) {
            $stock = BranchProductStock::query()->where('company_id', $return->company_id)->where('branch_id', $return->branch_id)->where('product_id', $row['product_id'])->lockForUpdate()->firstOrCreate([], ['company_id' => $return->company_id, 'branch_id' => $return->branch_id, 'product_id' => $row['product_id'], 'quantity' => 0]);
            app(StockMovementService::class)->move([
                'company_id' => $return->company_id,
                'branch_id' => $return->branch_id,
                'product_id' => $row['product_id'],
                'user_id' => $return->created_by,
                'reference_number' => $return->return_number,
                'type' => 'IN',
                'movement_type' => 'RETUR_CUSTOMER',
                'qty' => $row['quantity'],
                'notes' => 'Retur ' . $return->return_number,
            ]);
        }
    }

    private function validateReturnItems(SalesTransaction $transaction, array $items): Collection
    {
        $transaction->load(['details', 'returns.details']);
        $returned = $transaction->returns->where('status', 'completed')->flatMap->details->groupBy('product_id')->map(fn($details) => $details->sum('quantity'));
        return collect($items)->map(function (array $item) use ($transaction, $returned) {
            $detail = $transaction->details->firstWhere('product_id', $item['product_id']);
            $max = (int) ($detail?->quantity ?? 0) - (int) $returned->get($item['product_id'], 0);
            if (! $detail || (int) $item['quantity'] > $max) {
                throw ValidationException::withMessages(['items' => 'Jumlah retur melebihi kuota produk.']);
            }
            return ['product_id' => $detail->product_id, 'quantity' => $item['quantity'], 'unit_price' => $detail->unit_price, 'subtotal' => $item['quantity'] * $detail->unit_price];
        });
    }

    private function validateSettlement(string $resolution, array $data, int $returnTotal): void
    {
        $replacementTotal = 0;
        if ($resolution === 'ganti_produk' && ! empty($data['replacements'])) {
            $replacementTotal = collect($data['replacements'])->sum(fn(array $item) => $item['quantity'] * $item['unit_price']);
        }
        $totalSettlement = $replacementTotal;
        if (isset($data['settlement']) && $data['settlement'] === 'refund') {
            $totalSettlement += max(0, $returnTotal - $replacementTotal);
        } elseif ($resolution === 'refund') {
            $totalSettlement += $returnTotal;
        } elseif ($resolution === 'potong_tagihan') {
            $totalSettlement += $returnTotal;
        }
        if ($totalSettlement > $returnTotal + $replacementTotal) {
            throw ValidationException::withMessages(['settlement' => 'Total nilai penyelesaian tidak boleh melebihi Total Nilai Retur.']);
        }
    }

    private function computeReturnStatus(SalesTransaction $transaction, ?Collection $newRows = null, bool $isDraft = false): string
    {
        $transaction->load(['details', 'returns.details']);
        $returned = $transaction->returns->where('status', 'completed')->flatMap->details->groupBy('product_id')->map(fn($details) => $details->sum('quantity'));
        $totalPurchased = $transaction->details->sum('quantity');
        $totalReturned = $returned->sum();
        if ($totalReturned <= 0 && $isDraft) {
            return 'return';
        }
        if ($totalReturned >= $totalPurchased && $totalPurchased > 0) {
            return 'full_return';
        }
        return $totalReturned > 0 ? 'partial_return' : 'completed';
    }

    private function calculateSettlement(string $resolution, array $data, int $returnTotal): array
    {
        $replacements = [];
        $replacementTotal = 0;

        if ($resolution === 'ganti_produk' && ! empty($data['replacements'])) {
            $replacements = collect($data['replacements'])->map(function (array $item) use (&$replacementTotal) {
                $subtotal = $item['quantity'] * $item['unit_price'];
                $replacementTotal += $subtotal;
                return ['product_id' => $item['product_id'], 'quantity' => $item['quantity'], 'unit_price' => $item['unit_price'], 'subtotal' => $subtotal];
            })->all();
        }

        $refundAmount = 0;
        $customerCreditAmount = 0;
        $customerPaysAmount = 0;

        if ($resolution === 'refund') {
            $refundAmount = $returnTotal;
        } elseif ($resolution === 'ganti_produk') {
            $difference = $returnTotal - $replacementTotal;
            if ($difference > 0) {
                $settlementChoice = $data['settlement'] ?? 'refund';
                if ($settlementChoice === 'potong_tagihan') {
                    $customerCreditAmount = $difference;
                } else {
                    $refundAmount = $difference;
                }
            } elseif ($difference < 0) {
                $customerPaysAmount = abs($difference);
            }
        } elseif ($resolution === 'potong_tagihan') {
            $customerCreditAmount = $returnTotal;
        }

        return ['replacements' => $replacements, 'replacement_total' => $replacementTotal, 'refund_amount' => $refundAmount, 'customer_credit_amount' => $customerCreditAmount, 'customer_pays_amount' => $customerPaysAmount];
    }

    private function processResolution(SalesReturn $return, SalesTransaction $transaction, string $userId, array $settlement): void
    {
        if ($settlement['replacement_total'] > 0) {
            $this->processReplacementStockOut($return, $transaction, $settlement['replacements']);
        }
        if ($settlement['refund_amount'] > 0) {
            $this->recordRefundCashOut($return, $transaction, $userId, $settlement['refund_amount']);
        }
        if ($settlement['customer_credit_amount'] > 0 && $transaction->customer_id) {
            Customer::query()->where('id', $transaction->customer_id)->lockForUpdate()->increment('credit_balance', $settlement['customer_credit_amount']);
        }
        if ($settlement['customer_pays_amount'] > 0) {
            $this->recordReplacementCashIn($return, $transaction, $userId, $settlement['customer_pays_amount']);
        }
    }

    private function recordReplacementCashIn(SalesReturn $return, SalesTransaction $transaction, string $userId, int $amount): void
    {
        $setting = CashBankAccountSetting::query()
            ->where('company_id', $transaction->company_id)
            ->where('branch_id', $transaction->branch_id)
            ->where('can_receive_money', true)
            ->where('is_active', true)
            ->whereHas('account', fn($query) => $query->where('is_active', true))
            ->first()
            ?? CashBankAccountSetting::query()
            ->where('company_id', $transaction->company_id)
            ->whereNull('branch_id')
            ->where('can_receive_money', true)
            ->where('is_active', true)
            ->whereHas('account', fn($query) => $query->where('is_active', true))
            ->first();

        $accountId = $setting?->cash_bank_account_id;
        if ($accountId) {
            CashBankAccount::query()->lockForUpdate()->findOrFail($accountId)->increment('current_balance', $amount);
        }

        CashBankTransaction::create([
            'company_id' => $transaction->company_id,
            'cash_bank_account_id' => $accountId,
            'created_by' => $userId,
            'transaction_number' => $this->cashBankNumber($transaction->company_id),
            'type' => 'in',
            'category' => 'Retur Penjualan',
            'amount' => $amount,
            'transaction_date' => now()->toDateString(),
            'reference' => $return->id,
            'note' => 'Tagihan selisih pengganti retur ' . $return->return_number,
            'status' => 'completed',
        ]);
    }

    private function processReplacementStockOut(SalesReturn $return, SalesTransaction $transaction, array $replacements): void
    {
        foreach ($replacements as $item) {
            $stock = BranchProductStock::query()->where('company_id', $return->company_id)->where('branch_id', $return->branch_id)->where('product_id', $item['product_id'])->lockForUpdate()->first();
            if (! $stock || $stock->quantity < $item['quantity']) throw ValidationException::withMessages(['replacements' => 'Stok cabang tidak mencukupi untuk produk pengganti.']);
            app(StockMovementService::class)->move([
                'company_id' => $return->company_id,
                'branch_id' => $return->branch_id,
                'product_id' => $item['product_id'],
                'user_id' => $return->created_by,
                'reference_number' => $return->return_number,
                'type' => 'OUT',
                'movement_type' => 'RETUR_CUSTOMER',
                'qty' => $item['quantity'],
                'notes' => 'Pengganti retur ' . $return->return_number,
            ]);
        }
    }

    private function recordRefundCashOut(SalesReturn $return, SalesTransaction $transaction, string $userId, int $amount): void
    {
        $setting = CashBankAccountSetting::query()
            ->where('company_id', $transaction->company_id)
            ->where('branch_id', $transaction->branch_id)
            ->where('can_send_money', true)
            ->where('is_active', true)
            ->whereHas('account', fn($query) => $query->where('is_active', true))
            ->first()
            ?? CashBankAccountSetting::query()
            ->where('company_id', $transaction->company_id)
            ->whereNull('branch_id')
            ->where('can_send_money', true)
            ->where('is_active', true)
            ->whereHas('account', fn($query) => $query->where('is_active', true))
            ->first();

        $accountId = $setting?->cash_bank_account_id;
        if ($accountId) {
            CashBankAccount::query()->lockForUpdate()->findOrFail($accountId)->decrement('current_balance', $amount);
        }

        CashBankTransaction::create([
            'company_id' => $transaction->company_id,
            'cash_bank_account_id' => $accountId,
            'created_by' => $userId,
            'transaction_number' => $this->cashBankNumber($transaction->company_id),
            'type' => 'out',
            'category' => 'Retur Penjualan',
            'amount' => $amount,
            'transaction_date' => now()->toDateString(),
            'reference' => $return->id,
            'note' => 'Refund retur ' . $return->return_number,
            'status' => 'completed',
        ]);
    }

    public function completeReturn(SalesReturn $return, array $data = []): SalesReturn
    {
        return DB::transaction(function () use ($return, $data) {
            if ($return->status === 'completed') return $return;
            $return->load(['details', 'replacements', 'transaction.details', 'transaction.returns.details']);
            if ($data) {
                $rows = $this->validateReturnItems($return->transaction, $data['items']);
                $return->details()->delete();
                $return->details()->createMany($rows->all());
                $returnTotal = $rows->sum('subtotal');
                $resolution = $data['resolution'] ?? $return->resolution;
                $this->validateSettlement($resolution, $data, $returnTotal);
                $settlement = $this->calculateSettlement($resolution, $data, $returnTotal);
                $return->replacements()->delete();
                if (! empty($settlement['replacements'])) {
                    $return->replacements()->createMany($settlement['replacements']);
                }
                $return->update(['reason' => $data['reason'], 'resolution' => $resolution, 'note' => $data['note'] ?? null, 'total' => $returnTotal, 'refund_amount' => $settlement['refund_amount'], 'replacement_total' => $settlement['replacement_total'], 'customer_credit_amount' => $settlement['customer_credit_amount'], 'customer_pays_amount' => $settlement['customer_pays_amount']]);
            } else {
                $settlement = ['replacements' => $return->replacements->map(fn($r) => ['product_id' => $r->product_id, 'quantity' => $r->quantity, 'unit_price' => $r->unit_price, 'subtotal' => $r->subtotal])->all(), 'replacement_total' => (int) $return->replacement_total, 'refund_amount' => (int) $return->refund_amount, 'customer_credit_amount' => (int) $return->customer_credit_amount, 'customer_pays_amount' => (int) ($return->customer_pays_amount ?? 0)];
            }
            $return->load('details');
            $this->processReturnStockIn($return, $return->details->map(fn($detail) => ['product_id' => $detail->product_id, 'quantity' => $detail->quantity]));
            $this->processResolution($return, $return->transaction, (string) $return->created_by, $settlement);
            $return->update(['status' => 'completed']);
            $return->transaction->update(['status' => $this->computeReturnStatus($return->transaction)]);
            return $return->refresh();
        });
    }

    private function prepareDetails(string $companyId, string $branchId, array $items): Collection
    {
        return collect($items)->map(function (array $item) use ($companyId, $branchId) {
            $stock = BranchProductStock::query()->where('company_id', $companyId)->where('branch_id', $branchId)->where('product_id', $item['product_id'])->lockForUpdate()->first();
            if (! $stock || $stock->quantity < $item['quantity']) throw ValidationException::withMessages(['details' => 'Stok cabang tidak mencukupi untuk salah satu produk.']);
            return ['product_id' => $item['product_id'], 'quantity' => $item['quantity'], 'unit_price' => $item['unit_price'], 'subtotal' => $item['quantity'] * $item['unit_price']];
        });
    }

    private function recordSaleStockOut(SalesTransaction $transaction, Collection $details): void
    {
        foreach ($details as $row) {
            app(StockMovementService::class)->move([
                'company_id' => $transaction->company_id,
                'branch_id' => $transaction->branch_id,
                'product_id' => $row['product_id'],
                'user_id' => $transaction->created_by,
                'reference_number' => $transaction->transaction_number,
                'type' => 'OUT',
                'movement_type' => 'SALE',
                'qty' => $row['quantity'],
                'notes' => 'Penjualan POS ' . $transaction->transaction_number,
            ]);
        }
    }

    private function recordSaleStockIn(SalesTransaction $transaction, Collection $details, string $notePrefix = ''): void
    {
        foreach ($details as $row) {
            app(StockMovementService::class)->move([
                'company_id' => $transaction->company_id,
                'branch_id' => $transaction->branch_id,
                'product_id' => $row['product_id'],
                'user_id' => $transaction->created_by,
                'reference_number' => $transaction->transaction_number,
                'type' => 'IN',
                'movement_type' => 'VOID_SALE',
                'qty' => $row['quantity'],
                'notes' => $notePrefix . $transaction->transaction_number,
            ]);
        }
    }

    private function total(Collection $details, array $data): int
    {
        $subtotal = (int) $details->sum('subtotal');
        $discount = (int) ($data['discount'] ?? 0);
        $tax = (int) ($data['tax'] ?? 0);

        return max(0, $subtotal - $discount + (($data['tax_mode'] ?? 'exclusive') === 'inclusive' ? 0 : $tax));
    }

    private function recordCashBankTransaction(SalesTransaction $transaction, string $userId, int $amount, ?string $paymentMethod = null, ?string $proofFilePath = null): void
    {
        $category = 'Penjualan Tunai';
        $accountId = $this->resolveReceiveAccount($transaction, $paymentMethod, $category);

        if ($accountId) {
            CashBankAccount::where('id', $accountId)->lockForUpdate()->increment('current_balance', $amount);
        }

        CashBankTransaction::create([
            'company_id' => $transaction->company_id,
            'cash_bank_account_id' => $accountId,
            'created_by' => $userId,
            'transaction_number' => $this->cashBankNumber($transaction->company_id),
            'type' => 'in',
            'category' => $category,
            'amount' => $amount,
            'transaction_date' => now()->toDateString(),
            'reference' => $transaction->id,
            'note' => 'Penjualan ' . $transaction->transaction_number,
            'status' => 'completed',
            'proof_file_path' => $proofFilePath,
        ]);
    }

    private function recordPaymentCashIn(SalesTransaction $transaction, string $userId, int $amount, string $paymentNumber, ?string $paymentMethod = null, ?string $proofFilePath = null): void
    {
        $category = 'Pembayaran Penjualan';
        $accountId = $this->resolveReceiveAccount($transaction, $paymentMethod ?? $transaction->payment_method, $category);

        if ($accountId) {
            CashBankAccount::where('id', $accountId)->lockForUpdate()->increment('current_balance', $amount);
        }

        CashBankTransaction::create([
            'company_id' => $transaction->company_id,
            'cash_bank_account_id' => $accountId,
            'created_by' => $userId,
            'transaction_number' => $this->cashBankNumber($transaction->company_id),
            'type' => 'in',
            'category' => $category,
            'amount' => $amount,
            'transaction_date' => now()->toDateString(),
            'reference' => $transaction->id,
            'note' => 'Pembayaran ' . $paymentNumber . ' untuk ' . $transaction->transaction_number,
            'status' => 'completed',
            'proof_file_path' => $proofFilePath,
        ]);
    }

    private function resolveReceiveAccount(SalesTransaction $transaction, ?string $paymentMethod, string &$category): ?string
    {
        $companyId = (string) $transaction->company_id;
        $branchId = (string) $transaction->branch_id;
        $accountType = match ($paymentMethod) {
            'cash' => 'cash',
            'ewallet' => 'e_wallet',
            default => 'bank',
        };

        $priority = CashBankAccountSetting::query()
            ->join('cash_bank_accounts', 'cash_bank_accounts.id', '=', 'cash_bank_account_settings.cash_bank_account_id')
            ->where('cash_bank_account_settings.company_id', $companyId)
            ->where('cash_bank_account_settings.is_active', true)
            ->where('cash_bank_account_settings.can_receive_money', true)
            ->where('cash_bank_account_settings.is_default_receive', true)
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
            $category = $paymentMethod === 'cash' ? 'Penjualan Tunai' : 'Penerimaan Penjualan';
            return $priority->cash_bank_account_id;
        }

        $fallback = CashBankAccount::query()
            ->where('company_id', $companyId)
            ->where('type', $accountType)
            ->where('is_active', true)
            ->select('id')
            ->lockForUpdate()
            ->first();

        if ($fallback) {
            $category = $paymentMethod === 'cash' ? 'Penjualan Tunai' : 'Penerimaan Penjualan';
        }

        return $fallback?->id;
    }

    private function number(string $companyId): string
    {
        do {
            $number = 'INV-' . now()->format('ymd') . '-' . Str::upper(Str::random(5));
        } while (SalesTransaction::query()->where('company_id', $companyId)->where('transaction_number', $number)->exists());
        return $number;
    }

    private function cashBankNumber(string $companyId): string
    {
        do {
            $number = 'CB-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
        } while (CashBankTransaction::query()->where('company_id', $companyId)->where('transaction_number', $number)->exists());

        return $number;
    }

    private function returnNumber(string $companyId): string
    {
        do {
            $number = 'RET-' . now()->format('ymd') . '-' . Str::upper(Str::random(8));
        } while (SalesReturn::query()->where('company_id', $companyId)->where('return_number', $number)->exists());
        return $number;
    }
}
