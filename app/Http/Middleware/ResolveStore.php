<?php

namespace App\Http\Middleware;

use App\Models\Branch;
use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResolveStore
{
    public function handle(Request $request, Closure $next): Response
    {
        $companySlug = $request->route('company');
        $branchSlug = $request->route('branch');

        $query = Branch::query()
            ->select(['branches.id', 'branches.company_id', 'branches.name', 'branches.slug', 'branches.address', 'branches.city', 'branches.phone', 'branches.whatsapp_number'])
            ->join('companies', 'companies.id', '=', 'branches.company_id')
            ->addSelect(['companies.id as company_id', 'companies.name as company_name', 'companies.slug as company_slug', 'companies.logo_path'])
            ->where('companies.slug', $companySlug)
            ->where('branches.slug', $branchSlug)
            ->where('branches.status', Branch::STATUS_ACTIVE)
            ->where('branches.is_store_enabled', true);

        $store = $query->first();

        if (! $store) {
            abort(404);
        }

        // Pastikan toko online aktif (dari store_settings company).
        $company = Company::with('storeSetting')->find($store->company_id);
        $settings = $company?->storeSetting;

        if ($settings && ! $settings->is_store_active) {
            abort(404);
        }

        $request->attributes->set('store', $store);
        $request->attributes->set('store_settings', $settings);

        return $next($request);
    }
}
