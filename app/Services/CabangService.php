<?php

namespace App\Services;

use App\Models\Branch;
use Illuminate\Support\Facades\DB;

class CabangService
{
    public function create(string $companyId, array $data): Branch
    {
        return DB::transaction(fn (): Branch => Branch::create([
            'company_id' => $companyId,
            ...$this->mapAttributes($data),
        ]));
    }

    public function update(Branch $cabang, array $data): Branch
    {
        return DB::transaction(function () use ($cabang, $data): Branch {
            $cabang->update($this->mapAttributes($data));

            return $cabang->refresh();
        });
    }

    public function delete(Branch $cabang): void
    {
        DB::transaction(fn () => $cabang->delete());
    }

    private function mapAttributes(array $data): array
    {
        return [
            'code' => $data['kode'] ?? null,
            'name' => $data['nama'],
            'email' => $data['email'] ?? null,
            'phone' => $data['telepon'] ?? null,
            'address' => $data['alamat'] ?? null,
            'city' => $data['kota'] ?? null,
            'province' => $data['provinsi'] ?? null,
            'postal_code' => $data['kode_pos'] ?? null,
            'status' => $data['status'],
            'is_store_enabled' => isset($data['is_store_enabled']) ? (bool) $data['is_store_enabled'] : false,
        ];
    }
}