<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function view(User $user, Company $company): bool
    {
        return $user->can('company.view') && (string) $user->company_id === (string) $company->getKey();
    }

    public function update(User $user, Company $company): bool
    {
        return $user->can('company.edit') && (string) $user->company_id === (string) $company->getKey();
    }

    public function updateSettings(User $user, Company $company): bool
    {
        return $user->can('company.settings') && (string) $user->company_id === (string) $company->getKey();
    }

    public function updateLogo(User $user, Company $company): bool
    {
        return $user->can('company.logo') && (string) $user->company_id === (string) $company->getKey();
    }
}

