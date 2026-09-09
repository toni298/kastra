<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Session\Session;

class CompanyContext
{
    public const SESSION_KEY = 'company_context_id';

    private ?Company $company = null;

    public function resolve(User $user, Session $session): ?Company
    {
        $companyId = (string) ($user->company_id ?? '');

        if ($companyId === '') {
            $this->forget($session);

            return null;
        }

        $sessionCompanyId = $session->get(self::SESSION_KEY);

        if (! is_string($sessionCompanyId) || ! hash_equals($companyId, $sessionCompanyId)) {
            $session->put(self::SESSION_KEY, $companyId);
        }

        $company = Company::query()->find($companyId);

        if ($company === null) {
            $this->forget($session);

            return null;
        }

        $this->company = $company;

        return $company;
    }

    public function current(): ?Company
    {
        return $this->company;
    }

    public function id(): ?string
    {
        return $this->company === null
            ? null
            : (string) $this->company->getKey();
    }

    public function forget(Session $session): void
    {
        $session->forget(self::SESSION_KEY);
        $this->company = null;
    }

    public function clear(): void
    {
        $this->company = null;
    }
}