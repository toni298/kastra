<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class InventoryTransferAndStockOpnameSeeder extends Seeder
{
    private const COMPANY_ID = '019f9878-6c5f-7255-932f-79344fb36ce0';

    private const TRANSFER_COUNT = 10_000;

    private const OPNAME_COUNT = 5_000;

    private const INSERT_CHUNK_SIZE = 250;

    public function run(): void
    {
        $companyId = self::COMPANY_ID;
        $this->deleteExistingSeedData($companyId);
        $userIds = DB::table('users')->where('company_id', $companyId)->pluck('id')->all();
        $warehouseIds = DB::table('gudang')->where('company_id', $companyId)->where('aktif', true)->pluck('id')->all();
        $stocksByWarehouse = DB::table('product_stocks')
            ->where('company_id', $companyId)
            ->select(['id', 'gudang_id', 'quantity'])
            ->orderBy('gudang_id')
            ->get()
            ->groupBy('gudang_id')
            ->map(fn ($stocks) => $stocks->values()->all())
            ->all();

        if ($userIds === [] || count($warehouseIds) < 2 || $stocksByWarehouse === []) {
            throw new RuntimeException('Seeder inventory membutuhkan minimal satu user, dua gudang aktif, dan product stock pada company target.');
        }

        $this->seedTransfers($companyId, $userIds, $warehouseIds, $stocksByWarehouse);
        $this->seedStockOpnames($companyId, $userIds, $warehouseIds, $stocksByWarehouse);
    }

    private function deleteExistingSeedData(string $companyId): void
    {
        DB::transaction(function () use ($companyId): void {
            DB::table('transfer_transactions')
                ->where('company_id', $companyId)
                ->where('transfer_number', 'like', 'TRF-SEED-%')
                ->delete();

            DB::table('stock_opnames')
                ->where('company_id', $companyId)
                ->where('opname_number', 'like', 'SO-SEED-%')
                ->delete();
        });
    }

    /** @param list<string> $userIds @param list<string> $warehouseIds @param array<string, list<object>> $stocksByWarehouse */
    private function seedTransfers(string $companyId, array $userIds, array $warehouseIds, array $stocksByWarehouse): void
    {
        $transfers = [];
        $details = [];
        $timelines = [];

        for ($sequence = 1; $sequence <= self::TRANSFER_COUNT; $sequence++) {
            $sourceWarehouseId = $warehouseIds[array_rand($warehouseIds)];
            $destinationWarehouseId = $this->differentWarehouse($warehouseIds, $sourceWarehouseId);
            $transferId = (string) Str::uuid();
            $userId = $userIds[array_rand($userIds)];
            $date = $this->randomDate();
            $workflowStatus = $this->randomWorkflowStatus();
            $timestamp = $date->setTime(random_int(7, 19), random_int(0, 59), random_int(0, 59));

            $transfers[] = [
                'id' => $transferId,
                'company_id' => $companyId,
                'created_by' => $userId,
                'source_gudang_id' => $sourceWarehouseId,
                'destination_gudang_id' => $destinationWarehouseId,
                'destination_type' => 'gudang',
                'destination_branch_id' => null,
                'received_by' => $workflowStatus === 'in_transit' ? null : $userIds[array_rand($userIds)],
                'transfer_number' => sprintf('TRF-SEED-%s-%05d', $date->format('Ymd'), $sequence),
                'transfer_date' => $date->toDateString(),
                'status' => 'completed',
                'workflow_status' => $workflowStatus,
                'note' => 'Data performa seeder transfer.',
                'dispatched_at' => $timestamp,
                'received_at' => $workflowStatus === 'in_transit' ? null : $timestamp->addHours(random_int(1, 48)),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            $sourceStocks = $stocksByWarehouse[$sourceWarehouseId] ?? [];
            foreach ($this->randomStocks($sourceStocks, random_int(1, 5)) as $stock) {
                $quantity = random_int(1, max(1, min(20, (int) $stock->quantity ?: 1)));
                $receivedQuantity = match ($workflowStatus) {
                    'received' => $quantity,
                    'partially_received' => random_int(0, $quantity - 1),
                    default => 0,
                };

                $details[] = [
                    'id' => (string) Str::uuid(),
                    'transfer_transaction_id' => $transferId,
                    'source_stock_id' => $stock->id,
                    'destination_stock_id' => null,
                    'quantity' => $quantity,
                    'received_quantity' => $receivedQuantity,
                    'adjustment_note' => $workflowStatus === 'partially_received' ? 'Selisih simulasi seeder.' : null,
                    'received_at' => $workflowStatus === 'in_transit' ? null : $timestamp->addHours(random_int(1, 48)),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            $timelines[] = [
                'id' => (string) Str::uuid(),
                'transfer_transaction_id' => $transferId,
                'user_id' => $userId,
                'event' => 'dispatched',
                'note' => 'Transfer dikirim dari gudang asal.',
                'metadata' => json_encode(['seeded' => true]),
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            if (count($transfers) >= self::INSERT_CHUNK_SIZE) {
                $this->insertTransferChunk($transfers, $details, $timelines);
                [$transfers, $details, $timelines] = [[], [], []];
            }
        }

        $this->insertTransferChunk($transfers, $details, $timelines);
    }

    /** @param list<string> $userIds @param list<string> $warehouseIds @param array<string, list<object>> $stocksByWarehouse */
    private function seedStockOpnames(string $companyId, array $userIds, array $warehouseIds, array $stocksByWarehouse): void
    {
        $opnames = [];
        $details = [];

        for ($sequence = 1; $sequence <= self::OPNAME_COUNT; $sequence++) {
            $warehouseId = $warehouseIds[array_rand($warehouseIds)];
            $opnameId = (string) Str::uuid();
            $userId = $userIds[array_rand($userIds)];
            $date = $this->randomDate();
            $timestamp = $date->setTime(random_int(7, 19), random_int(0, 59), random_int(0, 59));
            $status = $this->randomOpnameStatus();

            $opnames[] = [
                'id' => $opnameId,
                'company_id' => $companyId,
                'source_type' => 'gudang',
                'gudang_id' => $warehouseId,
                'branch_id' => null,
                'created_by' => $userId,
                'completed_by' => $status === 'completed' ? $userIds[array_rand($userIds)] : null,
                'opname_number' => sprintf('SO-SEED-%s-%05d', $date->format('Ymd'), $sequence),
                'opname_date' => $date->toDateString(),
                'status' => $status,
                'note' => 'Data performa seeder stock opname.',
                'started_at' => $status === 'draft' ? null : $timestamp,
                'completed_at' => $status === 'completed' ? $timestamp->addHours(random_int(1, 8)) : null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];

            foreach ($this->randomStocks($stocksByWarehouse[$warehouseId] ?? [], random_int(3, 12)) as $stock) {
                $systemQuantity = (int) $stock->quantity;
                $physicalQuantity = $status === 'draft'
                    ? null
                    : max(0, $systemQuantity + random_int(-3, 3));

                $details[] = [
                    'id' => (string) Str::uuid(),
                    'stock_opname_id' => $opnameId,
                    'product_stock_id' => $stock->id,
                    'branch_product_stock_id' => null,
                    'system_quantity' => $systemQuantity,
                    'physical_quantity' => $physicalQuantity,
                    'note' => $physicalQuantity !== null && $physicalQuantity !== $systemQuantity ? 'Selisih simulasi seeder.' : null,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }

            if (count($opnames) >= self::INSERT_CHUNK_SIZE) {
                $this->insertOpnameChunk($opnames, $details);
                [$opnames, $details] = [[], []];
            }
        }

        $this->insertOpnameChunk($opnames, $details);
    }

    /** @param list<array<string, mixed>> $transfers @param list<array<string, mixed>> $details @param list<array<string, mixed>> $timelines */
    private function insertTransferChunk(array $transfers, array $details, array $timelines): void
    {
        if ($transfers === []) {
            return;
        }

        DB::transaction(function () use ($transfers, $details, $timelines): void {
            DB::table('transfer_transactions')->insert($transfers);
            DB::table('transfer_details')->insert($details);
            DB::table('transfer_timelines')->insert($timelines);
        });
    }

    /** @param list<array<string, mixed>> $opnames @param list<array<string, mixed>> $details */
    private function insertOpnameChunk(array $opnames, array $details): void
    {
        if ($opnames === []) {
            return;
        }

        DB::transaction(function () use ($opnames, $details): void {
            DB::table('stock_opnames')->insert($opnames);
            DB::table('stock_opname_details')->insert($details);
        });
    }

    /** @param list<string> $warehouseIds */
    private function differentWarehouse(array $warehouseIds, string $sourceWarehouseId): string
    {
        do {
            $destinationWarehouseId = $warehouseIds[array_rand($warehouseIds)];
        } while ($destinationWarehouseId === $sourceWarehouseId);

        return $destinationWarehouseId;
    }

    /** @param list<object> $stocks @return list<object> */
    private function randomStocks(array $stocks, int $count): array
    {
        shuffle($stocks);

        return array_slice($stocks, 0, min($count, count($stocks)));
    }

    private function randomDate(): CarbonImmutable
    {
        $start = CarbonImmutable::create(2023, 1, 1)->startOfDay();
        $end = now()->toImmutable()->endOfDay();

        return CarbonImmutable::createFromTimestamp(random_int($start->timestamp, $end->timestamp));
    }

    private function randomWorkflowStatus(): string
    {
        $statuses = ['in_transit', 'received', 'received', 'received', 'partially_received'];

        return $statuses[array_rand($statuses)];
    }

    private function randomOpnameStatus(): string
    {
        $statuses = ['draft', 'in_progress', 'completed', 'completed'];

        return $statuses[array_rand($statuses)];
    }
}
