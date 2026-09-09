<?php

namespace App\Policies;

use App\Models\ProductBrand;
use App\Models\User;

class ProductBrandPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('product_brands.view');
    }

    public function create(User $user): bool
    {
        return $user->can('product_brands.create');
    }

    public function update(User $user, ProductBrand $brand): bool
    {
        return $user->can('product_brands.edit')
            && $user->company_id === $brand->company_id;
    }

    public function delete(User $user, ProductBrand $brand): bool
    {
        return $user->can('product_brands.delete')
            && $user->company_id === $brand->company_id;
    }
}
