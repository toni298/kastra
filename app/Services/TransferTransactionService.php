<?php

namespace App\Services;

use App\Models\ProductStock;
use App\Models\BranchProductStock;
use App\Models\TransferTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TransferTransactionService
{
    public function create(string $companyId, string $userId, array $data): TransferTransaction
    {
        return DB::transaction(function () use ($companyId, $userId, $data) {
            $transaction = TransferTransaction::create([
                'company_id' => $companyId,
                'created_by' => $userId,
                'source_gudang_id' => $data['source_gudang_id'],
                'destination_gudang_id' => $data['destination_gudang_id'] ?? null,
                'destination_type' => $data['destination_type'],
                'destination_branch_id' => $data['destination_branch_id'] ?? null,
                'transfer_number' => $this->number($companyId),
                'transfer_date' => $data['transfer_date'],
                'status' => 'completed',
                'workflow_status' => 'in_transit',
                'note' => $data['note'] ?? null,
                'dispatched_at' => now(),
            ]);

            foreach ($data['details'] as $detail) {
                $source = ProductStock::query()->where('company_id', $companyId)
                    ->whereKey($detail['source_stock_id'])->where('gudang_id', $data['source_gudang_id'])
                    ->lockForUpdate()->first();

                if (! $source || $source->quantity < $detail['quantity']) {
                    throw ValidationException::withMessages([
                        'details' => 'Stok pada gudang asal tidak mencukupi untuk salah satu produk.',
                    ]);
                }

                app(StockMovementService::class)->move([
                    'company_id' => $companyId,
                    'gudang_id' => $data['source_gudang_id'],
                    'product_id' => $source->product_id,
                    'user_id' => $userId,
                    'reference_number' => $transaction->transfer_number,
                    'type' => 'OUT',
                    'movement_type' => 'TRANSFER_OUT',
                    'qty' => $detail['quantity'],
                    'notes' => 'Transfer keluar ' . $transaction->transfer_number,
                ]);
                $transaction->details()->create([
                    'source_stock_id' => $source->id,
                    'quantity' => $detail['quantity'],
                ]);
            }

            $transaction->timelines()->create(['user_id' => $userId, 'event' => 'dispatched', 'note' => 'Transfer dikirim dari gudang asal.']);
            return $transaction->load(['sourceGudang', 'destinationGudang', 'destinationBranch', 'details.sourceStock.product.unit', 'timelines.user']);
        });
    }

    public function receive(TransferTransaction $transaction, string $userId, array $data): TransferTransaction
    {
        return DB::transaction(function () use ($transaction, $userId, $data) {
            $transaction = TransferTransaction::query()
                ->whereKey($transaction->id)
                ->with(['details.sourceStock'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($transaction->workflow_status !== 'in_transit') {
                throw ValidationException::withMessages(['transfer' => 'Transfer ini sudah diproses sebelumnya.']);
            }

            // normalize inputs and collect details
            $detailInputs = collect($data['details'] ?? [])->keyBy('id');
            if ($detailInputs->isEmpty()) {
                throw ValidationException::withMessages(['details' => 'Tidak ada detail untuk diproses.']);
            }

            $lines = $transaction->details()->whereIn('id', $detailInputs->keys()->all())->with('sourceStock')->lockForUpdate()->get()->keyBy('id');

            $receivedPerProduct = [];
            foreach ($detailInputs as $id => $detail) {
                $line = $lines->get($id);
                if (! $line || ! $line->sourceStock) {
                    throw ValidationException::withMessages(['details' => 'Detail transfer tidak valid.']);
                }
                $received = (int) ($detail['received_quantity'] ?? 0);
                if ($received > $line->quantity) {
                    throw ValidationException::withMessages(['details' => 'Jumlah diterima tidak boleh melebihi jumlah dikirim.']);
                }
                $productId = $line->sourceStock->product_id;
                $receivedPerProduct[$productId] = ($receivedPerProduct[$productId] ?? 0) + $received;
            }

            $receivedTotal = 0;

            if ($transaction->destination_type === 'branch') {
                $branchId = $transaction->destination_branch_id;
                $productIds = array_keys($receivedPerProduct);

                foreach ($receivedPerProduct as $productId => $qty) {
                    $stock = BranchProductStock::query()
                        ->where('company_id', $transaction->company_id)
                        ->where('branch_id', $branchId)
                        ->where('product_id', $productId)
                        ->lockForUpdate()->first();
                    if (! $stock) {
                        $stock = BranchProductStock::create(['company_id' => $transaction->company_id, 'branch_id' => $branchId, 'product_id' => $productId, 'quantity' => 0]);
                    }
                    app(StockMovementService::class)->move([
                        'company_id' => $transaction->company_id,
                        'branch_id' => $branchId,
                        'product_id' => $productId,
                        'user_id' => $userId,
                        'reference_number' => $transaction->transfer_number,
                        'type' => 'IN',
                        'movement_type' => 'TRANSFER_IN',
                        'qty' => $qty,
                        'notes' => 'Transfer masuk ' . $transaction->transfer_number,
                    ]);
                }

                // update detail lines
                foreach ($detailInputs as $id => $detail) {
                    $line = $lines->get($id);
                    $received = (int) ($detail['received_quantity'] ?? 0);
                    $line->update([
                        'destination_stock_id' => null,
                        'received_quantity' => $received,
                        'adjustment_note' => $detail['adjustment_note'] ?? null,
                        'received_at' => now(),
                    ]);
                    $receivedTotal += $received;
                }
            } else {
                // destination is gudang, handle per-line as before
                foreach ($detailInputs as $id => $detail) {
                    $line = $lines->get($id);
                    $received = (int) ($detail['received_quantity'] ?? 0);

                    $destination = ProductStock::query()->where('company_id', $transaction->company_id)
                        ->where('gudang_id', $transaction->destination_gudang_id)
                        ->where('product_id', $line->sourceStock->product_id)
                        ->lockForUpdate()
                        ->first();
                    if (! $destination) {
                        $destination = ProductStock::create(['company_id' => $transaction->company_id, 'product_id' => $line->sourceStock->product_id, 'gudang_id' => $transaction->destination_gudang_id, 'quantity' => 0]);
                    }
                    if ($received > 0) {
                        app(StockMovementService::class)->move([
                            'company_id' => $transaction->company_id,
                            'gudang_id' => $transaction->destination_gudang_id,
                            'product_id' => $line->sourceStock->product_id,
                            'user_id' => $userId,
                            'reference_number' => $transaction->transfer_number,
                            'type' => 'IN',
                            'movement_type' => 'TRANSFER_IN',
                            'qty' => $received,
                            'notes' => 'Transfer masuk ' . $transaction->transfer_number,
                        ]);
                    }

                    $line->update([
                        'destination_stock_id' => $destination->id,
                        'received_quantity' => $received,
                        'adjustment_note' => $detail['adjustment_note'] ?? null,
                        'received_at' => now(),
                    ]);
                    $receivedTotal += $received;
                }
            }

            $allReceived = $transaction->details()->whereColumn('received_quantity', 'quantity')->count() === $transaction->details()->count();
            $transaction->update(['workflow_status' => $allReceived ? 'received' : 'partially_received', 'received_by' => $userId, 'received_at' => now()]);
            $transaction->timelines()->create(['user_id' => $userId, 'event' => 'received', 'note' => 'Transfer diterima dengan jumlah aktual.', 'metadata' => ['received_quantity' => $receivedTotal]]);
            if (! $allReceived) {
                $transaction->timelines()->create(['user_id' => $userId, 'event' => 'adjusted', 'note' => 'Jumlah diterima berbeda dari jumlah dikirim.']);
            }
            return $transaction->load(['sourceGudang', 'destinationGudang', 'destinationBranch', 'details.sourceStock.product.unit', 'timelines.user']);
        });
    }

    private function number(string $companyId): string
    {
        do {
            $number = 'TRF-' . now()->format('ymd') . '-' . Str::upper(Str::random(5));
        } while (TransferTransaction::query()->where('company_id', $companyId)->where('transfer_number', $number)->exists());

        return $number;
    }
}
