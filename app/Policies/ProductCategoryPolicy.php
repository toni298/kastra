<?php

namespace App\Policies;

use App\Models\ProductCategory;
use App\Models\User;

class ProductCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('product_categories.view');
    }

    public function create(User $user): bool
    {
        return $user->can('product_categories.create');
    }

    public function update(User $user, ProductCategory $category): bool
    {
        return $user->can('product_categories.edit')
            && $user->company_id === $category->company_id;
    }

    public function delete(User $user, ProductCategory $category): bool
    {
        return $user->can('product_categories.delete')
            && $user->company_id === $category->company_id;
    }
}
