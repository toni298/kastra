<?php

namespace App\Services;

use App\Repositories\ProductMasterRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProductMasterService
{
    public function __construct(private ProductMasterRepository $repository) {}

    public function createModel(string $master, string $companyId, array $data): Model
    {
        return DB::transaction(function () use ($master, $companyId, $data) {
            $model = $this->repository->modelClass($master);
            $attributes = [
                'company_id' => $companyId,
                'name' => $data['name'],
                'is_active' => $data['is_active'] ?? true,
            ];

            if ($master === 'units') {
                $attributes['code'] = $data['code'];
            }

            return $model::create($attributes);
        });
    }

    public function updateModel(Model $model, array $data): Model
    {
        return DB::transaction(function () use ($model, $data) {
            $model->update($data);

            return $model->refresh();
        });
    }

    public function deleteModel(Model $model): void
    {
        abort_if(method_exists($model, 'products') && $model->products()->exists(), 422, 'Data masih digunakan produk.');
        DB::transaction(fn () => $model->delete());
    }
}
