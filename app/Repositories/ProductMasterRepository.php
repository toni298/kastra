<?php

namespace App\Repositories;

use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

class ProductMasterRepository
{
    public const MASTERS = [
        'product_categories',
        'product_brands',
        'units',
    ];

    /**
     * @return class-string<Model>
     */
    public function modelClass(string $master): string
    {
        return match ($master) {
            'product_categories' => ProductCategory::class,
            'product_brands' => ProductBrand::class,
            'units' => Unit::class,
            default => abort(404),
        };
    }

    public function paginate(string $master, string $companyId, array $filters): LengthAwarePaginator
    {
        $model = $this->modelClass($master);
        $direction = $filters['sort_direction'] ?? 'asc';

        return $model::query()
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn($query, $search) => $query
                ->where(fn($nested) => $nested
                    ->where('name', 'like', "%{$search}%")
                    ->when($master === 'units', fn($unitQuery) => $unitQuery
                        ->orWhere('code', 'like', "%{$search}%"))))
            ->when($filters['status'] ?? null, fn($query, $status) => $query
                ->where('is_active', $status === 'active'))
            ->withCount('products')
            ->orderBy($filters['sort'] ?? 'name', $direction)
            ->orderBy('id', $direction)
            ->paginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function cursorPaginate(string $master, string $companyId, array $filters): CursorPaginator
    {
        $model = $this->modelClass($master);
        $direction = $filters['sort_direction'] ?? 'asc';

        return $model::query()
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn($query, $search) => $query
                ->where(fn($nested) => $nested
                    ->where('name', 'like', "%{$search}%")
                    ->when($master === 'units', fn($unitQuery) => $unitQuery
                        ->orWhere('code', 'like', "%{$search}%"))))
            ->when($filters['status'] ?? null, fn($query, $status) => $query->where('is_active', $status === 'active'))
            ->withCount('products')
            ->orderBy($filters['sort'] ?? 'name', $direction)
            ->orderBy('id', $direction)
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function find(string $master, string $companyId, string $id): Model
    {
        $model = $this->modelClass($master);

        return $model::query()
            ->where('company_id', $companyId)
            ->findOrFail($id);
    }

    public function search(string $master, string $companyId, array $filters): CursorPaginator
    {
        $model = $this->modelClass($master);

        return $model::query()
            ->select(['id', 'name'])
            ->where('company_id', $companyId)
            ->where('is_active', true)
            ->when($filters['search'] ?? null, fn($query, $search) => $query
                ->where(fn($nested) => $nested
                    ->where('name', 'like', "%{$search}%")
                    ->when($master === 'units', fn($unitQuery) => $unitQuery
                        ->orWhere('code', 'like', "%{$search}%"))))
            ->orderBy('name')
            ->orderBy('id')
            ->cursorPaginate(20);
    }
}
