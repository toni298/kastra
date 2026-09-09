<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;
use App\Services\InertiaAuthorizationService;
use App\Services\MenuService;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        if ($request->routeIs('welcome') || $request->routeIs('store.*')) {
            return [
                ...parent::share($request),
                'auth' => ['user' => null],
                'flash' => [
                    'error' => fn() => $request->session()->pull('error'),
                ],
            ];
        }

        $user = $request->user();
        $authorization = $user
            ? app(InertiaAuthorizationService::class)->for($user)
            : ['roles' => [], 'permissions' => []];

        $menus = $user
            ? app(MenuService::class)->forUser($user, $authorization['permissions'])
            : [];

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    ...$user->only('id', 'name', 'email', 'company_id'),
                ] : null,
                'roles' => $authorization['roles'],
                'permissions' => $authorization['permissions'],
            ],
            'menus' => fn() => $menus,
            'salesTabs' => fn() => app(MenuService::class)->salesTabs(),
            'context' => [
                'company' => fn() => $request->attributes
                    ->get('current_company')
                    ?->only('id', 'name', 'organization_mode', 'logo_path'),
            ],
            'ziggy' => fn() => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'success' => fn() => $request->session()->pull('success'),
                'error' => fn() => $request->session()->pull('error'),
                'warning' => fn() => $request->session()->pull('warning'),
                'info' => fn() => $request->session()->pull('info'),
                'customer' => fn() => $request->session()->pull('customer'),
                'supplier' => fn() => $request->session()->pull('supplier'),
                'supplierDetail' => fn() => $request->session()->pull('supplierDetail'),
                'product_categories' => fn() => $request->session()->pull('product_categories'),
                'product_brands' => fn() => $request->session()->pull('product_brands'),
                'units' => fn() => $request->session()->pull('units'),
                'invoiceData' => fn() => $request->session()->pull('invoiceData'),
            ],
        ];
    }
}
