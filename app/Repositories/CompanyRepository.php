<?php

namespace App\Repositories;

use App\Models\Company;

class CompanyRepository
{
    public function find(string $companyId): Company
    {
        return Company::query()->findOrFail($companyId);
    }
}
