<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PurchaseTransactionPerformanceSeeder extends Seeder
{
    private const COMPANY_ID = '019f9878-6c5f-7255-932f-79344fb36ce0';
    private const TRANSACTION_COUNT = 10_000;
    private const CHUNK_SIZE = 250;

    public function run(): void
    {
        $companyId = self::COMPANY_ID;
        $this->deleteExistingSeedData($companyId);
        $userIds = DB::table('users')->where('company_id', $companyId)->pluck('id')->all();
        $supplierIds = DB::table('suppliers')->where('company_id', $companyId)->pluck('id')->all();
        $stocksByWarehouse = DB::table('product_stocks')->where('company_id', $companyId)->select(['gudang_id', 'product_id'])->get()->groupBy('gudang_id')->map(fn ($stocks) => $stocks->values()->all())->all();

        if ($userIds === [] || $supplierIds === [] || $stocksByWarehouse === []) {
            throw new RuntimeException('Seeder pembelian membutuhkan user, supplier, dan product stock pada company target.');
        }

        [$transactions, $details, $payments] = [[], [], []];
        for ($sequence = 1; $sequence <= self::TRANSACTION_COUNT; $sequence++) {
            $gudangId = array_rand($stocksByWarehouse);
            $transactionId = (string) Str::uuid();
            $userId = $userIds[array_rand($userIds)];
            $date = $this->randomDate();
            $timestamp = $date->setTime(random_int(7, 20), random_int(0, 59), random_int(0, 59));
            $status = $this->randomStatus();
            $paymentStatus = $status === 'draft' ? 'unpaid' : ['paid', 'partial', 'unpaid'][array_rand(['paid', 'partial', 'unpaid'])];
            $lineItems = [];
            $stocks = $stocksByWarehouse[$gudangId];
            shuffle($stocks);

            foreach (array_slice($stocks, 0, min(random_int(1, 4), count($stocks))) as $stock) {
                $quantity = random_int(1, 20);
                $unitPrice = random_int(5_000, 300_000);
                $discount = random_int(0, 10) === 0 ? (int) floor($quantity * $unitPrice * 0.05) : 0;
                $lineItems[] = ['id' => (string) Str::uuid(), 'purchase_transaction_id' => $transactionId, 'product_id' => $stock->product_id, 'quantity' => $quantity, 'unit_price' => $unitPrice, 'discount' => $discount, 'subtotal' => max(0, $quantity * $unitPrice - $discount), 'created_at' => $timestamp, 'updated_at' => $timestamp];
            }

            $subtotal = array_sum(array_column($lineItems, 'subtotal'));
            $discount = random_int(0, 10) === 0 ? (int) floor($subtotal * 0.03) : 0;
            $tax = (int) floor(max(0, $subtotal - $discount) * 0.11);
            $total = max(0, $subtotal - $discount + $tax);
            $paidAmount = $paymentStatus === 'paid' ? $total : ($paymentStatus === 'partial' ? (int) floor($total * random_int(10, 80) / 100) : 0);

            $transactions[] = ['id' => $transactionId, 'company_id' => $companyId, 'supplier_id' => $supplierIds[array_rand($supplierIds)], 'gudang_id' => $gudangId, 'created_by' => $userId, 'transaction_number' => sprintf('PUR-SEED-%s-%05d', $date->format('Ymd'), $sequence), 'document_type' => random_int(1, 100) <= 45 ? 'purchase_order' : 'purchase_invoice', 'status' => $status, 'payment_status' => $paymentStatus, 'transaction_date' => $date->toDateString(), 'due_date' => $date->addDays(random_int(7, 45))->toDateString(), 'discount' => $discount, 'tax' => $tax, 'total' => $total, 'note' => 'Data performa seeder transaksi pembelian.', 'created_at' => $timestamp, 'updated_at' => $timestamp];
            array_push($details, ...$lineItems);

            if ($paidAmount > 0) {
                $payments[] = ['id' => (string) Str::uuid(), 'company_id' => $companyId, 'purchase_transaction_id' => $transactionId, 'created_by' => $userId, 'user_id' => $userId, 'payment_number' => sprintf('PPY-SEED-%s-%05d', $date->format('Ymd'), $sequence), 'payment_method' => ['transfer_bank', 'tunai', 'giro'][array_rand(['transfer_bank', 'tunai', 'giro'])], 'amount' => $paidAmount, 'payment_date' => $date->toDateString(), 'reference' => null, 'note' => 'Data performa seeder pembayaran pembelian.', 'created_at' => $timestamp, 'updated_at' => $timestamp];
            }

            if (count($transactions) >= self::CHUNK_SIZE) {
                $this->insertChunk($transactions, $details, $payments);
                [$transactions, $details, $payments] = [[], [], []];
            }
        }
        $this->insertChunk($transactions, $details, $payments);
    }

    private function deleteExistingSeedData(string $companyId): void
    {
        DB::table('purchase_transactions')->where('company_id', $companyId)->where('transaction_number', 'like', 'PUR-SEED-%')->delete();
    }

    /** @param list<array<string, mixed>> $transactions @param list<array<string, mixed>> $details @param list<array<string, mixed>> $payments */
    private function insertChunk(array $transactions, array $details, array $payments): void
    {
        if ($transactions === []) return;
        DB::transaction(function () use ($transactions, $details, $payments): void {
            DB::table('purchase_transactions')->insert($transactions);
            DB::table('purchase_transaction_details')->insert($details);
            if ($payments !== []) DB::table('purchase_payments')->insert($payments);
        });
    }

    private function randomDate(): CarbonImmutable
    {
        $start = CarbonImmutable::create(2023, 1, 1)->startOfDay();
        return CarbonImmutable::createFromTimestamp(random_int($start->timestamp, now()->timestamp));
    }

    private function randomStatus(): string
    {
        $statuses = ['completed', 'completed', 'ordered', 'received', 'draft', 'returned', 'cancelled'];
        return $statuses[array_rand($statuses)];
    }
}
