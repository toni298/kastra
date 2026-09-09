<?php

namespace App\Http\Middleware;

use App\Services\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveCompanyContext
{
    public function __construct(
        private CompanyContext $companyContext,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $company = $this->companyContext->resolve($user, $request->session());

        if ($company === null) {
            return $this->handleMissingCompany($request);
        }

        $request->attributes->set('current_company', $company);

        try {
            return $next($request);
        } finally {
            $this->companyContext->clear();
        }
    }

    private function handleMissingCompany(Request $request): never
    {
        $request->session()->forget(CompanyContext::SESSION_KEY);

        abort(
            Response::HTTP_FORBIDDEN,
            'Konteks perusahaan untuk akun ini tidak tersedia.',
        );
    }
}