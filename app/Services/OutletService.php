<?php

namespace App\Services;

use App\Models\Outlet;
use Illuminate\Support\Facades\DB;

class OutletService
{
    public function create(string $companyId, array $data): Outlet
    {
        return DB::transaction(fn () => Outlet::create([
            ...$this->mapBranch($data),
            'company_id' => $companyId,
        ]));
    }

    public function update(Outlet $outlet, array $data): Outlet
    {
        return DB::transaction(function () use ($outlet, $data) {
            $outlet->update($this->mapBranch($data));

            return $outlet->refresh();
        });
    }

    public function delete(Outlet $outlet): void
    {
        DB::transaction(fn () => $outlet->delete());
    }

    private function mapBranch(array $data): array
    {
        $data['branch_id'] = $data['cabang_id'] ?? null;
        unset($data['cabang_id']);

        return $data;
    }
}
