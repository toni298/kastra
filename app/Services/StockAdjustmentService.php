<?php

namespace App\Services;

use App\Models\BranchProductStock;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentService
{
    /**
     * Sesuaikan stok cabang (tambah/kurangi) dan catat ke StockMovement.
     *
     * @param  string  $companyId
     * @param  string  $branchId
     * @param  string  $productId
     * @param  string  $type       'in' (masuk) | 'out' (keluar)
     * @param  int     $quantity   jumlah (positif)
     * @param  string|null  $note  alasan penyesuaian
     * @return BranchProductStock  stok setelah penyesuaian
     *
     * @throws ValidationException bila stok keluar melebihi sisa stok
     */
    public function adjust(
        string $companyId,
        string $branchId,
        string $productId,
        string $type,
        int $quantity,
        ?string $note = null
    ): BranchProductStock {
        return DB::transaction(function () use ($companyId, $branchId, $productId, $type, $quantity, $note) {
            $stock = BranchProductStock::query()
                ->where('company_id', $companyId)
                ->where('branch_id', $branchId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (! $stock) {
                // Buat baris stok baru bila belum ada (khusus penambahan).
                if ($type === 'out') {
                    throw ValidationException::withMessages([
                        'quantity' => 'Stok produk di cabang ini belum ada / kosong.',
                    ]);
                }
                $stock = BranchProductStock::create([
                    'company_id' => $companyId,
                    'branch_id' => $branchId,
                    'product_id' => $productId,
                    'quantity' => 0,
                    'discount' => 0,
                ]);
            }

            app(StockMovementService::class)->move([
                'company_id' => $companyId,
                'branch_id' => $branchId,
                'user_id' => request()->user()?->getAuthIdentifier(),
                'product_id' => $productId,
                'type' => $type === 'in' ? 'IN' : 'OUT',
                'movement_type' => 'OPNAME_ADJUSTMENT',
                'qty' => $quantity,
                'reference_number' => 'ADJ-' . now()->format('YmdHis'),
                'notes' => $note ?: ($type === 'in' ? 'Penyesuaian stok masuk' : 'Penyesuaian stok keluar'),
            ]);

            return $stock->refresh();
        });
    }
}
