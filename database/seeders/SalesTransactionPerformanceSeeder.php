<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class SalesTransactionPerformanceSeeder extends Seeder
{
    private const COMPANY_ID = '019f9878-6c5f-7255-932f-79344fb36ce0';

    private const TRANSACTION_COUNT = 10_000;

    private const CHUNK_SIZE = 250;

    public function run(): void
    {
        $companyId = self::COMPANY_ID;
        $this->deleteExistingSeedData($companyId);

        $userIds = DB::table('users')->where('company_id', $companyId)->pluck('id')->all();
        $customerIds = DB::table('customers')->where('company_id', $companyId)->pluck('id')->all();
        $stocksByBranch = DB::table('branch_product_stocks')
            ->where('company_id', $companyId)
            ->select(['branch_id', 'product_id'])
            ->get()
            ->groupBy('branch_id')
            ->map(fn ($stocks) => $stocks->values()->all())
            ->all();

        if ($userIds === [] || $stocksByBranch === []) {
            throw new RuntimeException('Seeder penjualan membutuhkan minimal satu user dan branch product stock pada company target.');
        }

        $transactions = [];
        $details = [];
        $payments = [];

        for ($sequence = 1; $sequence <= self::TRANSACTION_COUNT; $sequence++) {
            $branchId = array_rand($stocksByBranch);
            $transactionId = (string) Str::uuid();
            $userId = $userIds[array_rand($userIds)];
            $date = $this->randomDate();
            $timestamp = $date->setTime(random_int(7, 21), random_int(0, 59), random_int(0, 59));
            $status = $this->randomStatus();
            $paymentStatus = $status === 'draft' ? 'unpaid' : (random_int(1, 100) <= 70 ? 'paid' : 'unpaid');
            $transactionDetails = [];

            $stocks = $stocksByBranch[$branchId];
            shuffle($stocks);
            foreach (array_slice($stocks, 0, min(random_int(1, 3), count($stocks))) as $stock) {
                $quantity = random_int(1, 5);
                $unitPrice = random_int(10_000, 250_000);
                $transactionDetails[] = [
                    'id' => (string) Str::uuid(),
                    'sales_transaction_id' => $transactionId,
                    'product_id' => $stock->product_id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $quantity * $unitPrice,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            $subtotal = array_sum(array_column($transactionDetails, 'subtotal'));
            $discount = random_int(0, 10) === 0 ? (int) floor($subtotal * 0.05) : 0;
            $tax = (int) floor(max(0, $subtotal - $discount) * 0.11);
            $total = max(0, $subtotal - $discount + $tax);
            $paidAmount = $paymentStatus === 'paid' ? $total : (int) floor($total * random_int(0, 70) / 100);

            $transactions[] = [
                'id' => $transactionId,
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'customer_id' => $customerIds === [] || random_int(1, 100) <= 20 ? null : $customerIds[array_rand($customerIds)],
                'created_by' => $userId,
                'transaction_number' => sprintf('INV-SEED-%s-%05d', $date->format('Ymd'), $sequence),
                'document_type' => 'invoice',
                'transaction_date' => $date->toDateString(),
                'status' => $status,
                'payment_status' => $paymentStatus,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'note' => 'Data performa seeder transaksi penjualan.',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
            array_push($details, ...$transactionDetails);

            if ($status !== 'draft') {
                $payments[] = [
                    'id' => (string) Str::uuid(),
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'sales_transaction_id' => $transactionId,
                    'payment_number' => sprintf('PAY-SEED-%s-%05d', $date->format('Ymd'), $sequence),
                    'payment_method' => ['cash', 'transfer', 'qris'][array_rand(['cash', 'transfer', 'qris'])],
                    'amount' => $paidAmount,
                    'payment_status' => $paymentStatus === 'paid' ? 'paid' : 'pending',
                    'payment_date' => $date->toDateString(),
                    'reference_number' => null,
                    'note' => 'Data performa seeder pembayaran.',
                    'received_by' => $userId,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
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
        DB::table('sales_transactions')
            ->where('company_id', $companyId)
            ->where('transaction_number', 'like', 'INV-SEED-%')
            ->delete();
    }

    /** @param list<array<string, mixed>> $transactions @param list<array<string, mixed>> $details @param list<array<string, mixed>> $payments */
    private function insertChunk(array $transactions, array $details, array $payments): void
    {
        if ($transactions === []) {
            return;
        }

        DB::transaction(function () use ($transactions, $details, $payments): void {
            DB::table('sales_transactions')->insert($transactions);
            DB::table('sales_transaction_details')->insert($details);

            if ($payments !== []) {
                DB::table('sales_payments')->insert($payments);
            }
        });
    }

    private function randomDate(): CarbonImmutable
    {
        $start = CarbonImmutable::create(2023, 1, 1)->startOfDay();
        $end = now()->toImmutable()->endOfDay();

        return CarbonImmutable::createFromTimestamp(random_int($start->timestamp, $end->timestamp));
    }

    private function randomStatus(): string
    {
        $statuses = ['completed', 'completed', 'completed', 'draft', 'return', 'cancelled'];

        return $statuses[array_rand($statuses)];
    }
}
