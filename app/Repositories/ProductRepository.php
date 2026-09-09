<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    public function paginate(string $companyId, array $filters)
    {
        $sort = $filters['sort'] ?? null;
        $direction = $filters['sort_direction'] ?? 'asc';

        return Product::query()
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
}
