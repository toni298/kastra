<?php

namespace App\Services;

use App\Models\ProductStock;
use App\Models\BranchProductStock;
use App\Models\StockOpname;
use App\Repositories\StockOpnameRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockOpnameService
{
    public function __construct(private StockOpnameRepository $repository) {}

    public function create(string $companyId, string $userId, array $data): StockOpname
    {
        return DB::transaction(function () use ($companyId, $userId, $data) {
            $isBranch = $data['source_type'] === 'cabang';
            $stocks = $isBranch
                ? BranchProductStock::query()->with('product')->where('company_id', $companyId)->where('branch_id', $data['branch_id'])->get()
                : ProductStock::query()->with('product')->where('company_id', $companyId)->where('gudang_id', $data['gudang_id'])->get();
            if ($stocks->isEmpty()) throw ValidationException::withMessages([$isBranch ? 'branch_id' : 'gudang_id' => $isBranch ? 'Cabang belum memiliki stok produk.' : 'Gudang belum memiliki stok produk.']);
            $opname = StockOpname::create(['company_id' => $companyId, 'source_type' => $data['source_type'], 'gudang_id' => $data['gudang_id'] ?? null, 'branch_id' => $data['branch_id'] ?? null, 'created_by' => $userId, 'opname_number' => $this->number($companyId), 'opname_date' => $data['opname_date'], 'status' => 'in_progress', 'started_at' => now(), 'note' => $data['note'] ?? null]);
            $opname->details()->createMany($stocks->map(fn($stock) => ['product_stock_id' => $isBranch ? null : $stock->id, 'branch_product_stock_id' => $isBranch ? $stock->id : null, 'system_quantity' => $stock->quantity])->all());
            return $opname;
        });
    }

    public function save(StockOpname $opname, string $userId, array $data, bool $complete = false): StockOpname
    {
        return DB::transaction(function () use ($opname, $userId, $data, $complete) {
            $opname = StockOpname::query()->whereKey($opname->id)->lockForUpdate()->firstOrFail();
            if (! in_array($opname->status, ['draft', 'in_progress'], true)) throw ValidationException::withMessages(['opname' => 'Stock opname sudah tidak dapat diubah.']);
            $isBranch = $opname->source_type === 'cabang';
            foreach ($data['details'] as $detail) $opname->details()->whereKey($detail['id'])->update(['physical_quantity' => $detail['physical_quantity'] ?? null, 'note' => $detail['note'] ?? null]);
            $opname->update(['note' => $data['note'] ?? $opname->note, 'status' => $complete ? 'completed' : ($data['status'] ?? 'in_progress'), 'completed_by' => $complete ? $userId : null, 'completed_at' => $complete ? now() : null]);
            if ($complete) {
                $details = $opname->details()->lockForUpdate()->get();
                if ($details->contains(fn($detail) => $detail->physical_quantity === null)) throw ValidationException::withMessages(['details' => 'Semua stok fisik wajib diisi sebelum opname diselesaikan.']);
                foreach ($details as $detail) {
                    $model = $opname->source_type === 'cabang' ? BranchProductStock::class : ProductStock::class;
                    $stockId = $opname->source_type === 'cabang' ? $detail->branch_product_stock_id : $detail->product_stock_id;
                    $stock = $model::query()->whereKey($stockId)->lockForUpdate()->firstOrFail();
                    $difference = (int) $detail->physical_quantity - (int) $stock->quantity;
                    if ($difference !== 0) {
                        app(StockMovementService::class)->move([
                            'company_id' => $opname->company_id,
                            'branch_id' => $isBranch ? $stock->branch_id : null,
                            'gudang_id' => $isBranch ? null : $stock->gudang_id,
                            'product_id' => $stock->product_id,
                            'user_id' => $userId,
                            'reference_number' => $opname->opname_number,
                            'type' => $difference > 0 ? 'IN' : 'OUT',
                            'movement_type' => 'OPNAME_ADJUSTMENT',
                            'qty' => abs($difference),
                            'notes' => $detail->note ?: 'Koreksi stock opname ' . $opname->opname_number,
                        ]);
                    }
                }
            }
            return $opname;
        });
    }

    public function delete(StockOpname $opname): void
    {
        DB::transaction(fn() => $opname->delete());
    }

    private function number(string $companyId): string
    {
        do {
            $number = 'SO-' . now()->format('ymd') . '-' . Str::upper(Str::random(5));
        } while (StockOpname::query()->where('company_id', $companyId)->where('opname_number', $number)->exists());
        return $number;
    }
}
