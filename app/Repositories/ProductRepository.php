<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductRepository
{
    private const CACHE_VERSION_PREFIX = 'products:index:version:';

    private const LIST_COLUMNS = [
        'id',
        'category_id',
        'brand_id',
        'unit_id',
        'name',
        'sku',
        'barcode',
        'purchase_price',
        'selling_price',
        'minimum_stock',
        'description',
        'is_active',
        'created_at',
    ];

    public function paginate(string $companyId, array $filters)
    {
        $sort = $filters['sort'] ?? null;
        $direction = $filters['sort_direction'] ?? 'asc';

        return Product::query()
            ->select(self::LIST_COLUMNS)
            ->with([
                'category:id,name',
                'brand:id,name',
                'unit:id,name',
                'images:id,product_id,original_path,webp_path,thumbnail_path,status,sort_order',
            ])
            ->where('company_id', $companyId)
            ->when($filters['search'] ?? null, fn($query, $search) => $query
                ->where(fn($nested) => $nested
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")))
            ->when(
                $sort,
                fn($query) => $query->orderBy($sort, $direction)->orderBy('id', $direction),
                fn($query) => $query->orderByDesc('created_at')->orderByDesc('id'),
            )
            ->cursorPaginate($filters['per_page'] ?? 10)
            ->withQueryString();
    }

    public function cacheKey(string $companyId, array $filters): string
    {
        $version = (int) Cache::get(self::CACHE_VERSION_PREFIX . $companyId, 1);

        return 'products:index:' . $companyId . ':' . $version . ':' . hash(
            'sha256',
            json_encode($filters, JSON_THROW_ON_ERROR),
        );
    }

    public function invalidateCache(string $companyId): void
    {
        $versionKey = self::CACHE_VERSION_PREFIX . $companyId;

        Cache::add($versionKey, 1, now()->addYear());
        Cache::increment($versionKey);
    }
}
