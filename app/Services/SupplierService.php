<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplierService
{
    public function create(string $companyId, string $userId, array $data): Supplier
    {
        return DB::transaction(function () use ($companyId, $userId, $data) {
            $supplier = Supplier::create([...$data, 'company_id' => $companyId]);
            $this->log('created', $supplier, $userId);

            return $supplier;
        });
    }

    public function update(Supplier $supplier, string $userId, array $data): Supplier
    {
        return DB::transaction(function () use ($supplier, $userId, $data) {
            $supplier->update($data);
            $this->log('updated', $supplier, $userId);

            return $supplier->refresh();
        });
    }

    public function delete(Supplier $supplier, string $userId): void
    {
        DB::transaction(function () use ($supplier, $userId) {
            $this->log('deleted', $supplier, $userId);
            $supplier->delete();
        });
    }

    private function log(string $action, Supplier $supplier, string $userId): void
    {
        Log::info("Supplier {$action}", [
            'supplier_id' => $supplier->id,
            'user_id' => $userId,
            'company_id' => $supplier->company_id,
        ]);
    }
}
