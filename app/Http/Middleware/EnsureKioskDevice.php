<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureKioskDevice
{
    public function __construct(private CompanyContext $companyContext) {}

    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = (string) config('kiosk.device_token');
        $requestToken = (string) ($request->header('X-Kiosk-Token') ?: $request->header('X-Kiosk-Api-Key'));

        abort_unless($configuredToken !== '' && $requestToken !== '' && hash_equals($configuredToken, $requestToken), 401, 'Perangkat kiosk tidak terautentikasi.');

        $allowedIps = config('kiosk.allowed_ips', []);
        abort_unless($allowedIps === [] || in_array($request->ip(), $allowedIps, true), 403, 'Alamat IP perangkat kiosk tidak diizinkan.');

        $configuredLatitude = config('kiosk.latitude');
        $configuredLongitude = config('kiosk.longitude');
        if ($configuredLatitude !== null && $configuredLongitude !== null) {
            abort_unless($request->filled(['latitude', 'longitude']), 403, 'Koordinat kiosk wajib dikirim.');
            abort_unless($this->withinRadius(
                (float) $request->input('latitude'),
                (float) $request->input('longitude'),
                (float) $configuredLatitude,
                (float) $configuredLongitude,
            ), 403, 'Lokasi perangkat kiosk berada di luar area yang diizinkan.');
        }

        $companyId = (string) config('kiosk.company_id');
        $company = $companyId !== '' ? Company::query()->find($companyId) : null;
        abort_unless($company !== null, 503, 'Konfigurasi perusahaan kiosk belum tersedia.');

        $this->companyContext->set($company);
        $request->attributes->set('current_company', $company);

        try {
            return $next($request);
        } finally {
            $this->companyContext->clear();
        }
    }

    private function withinRadius(float $latitude, float $longitude, float $targetLatitude, float $targetLongitude): bool
    {
        $earthRadius = 6371000;
        $latitudeDelta = deg2rad($targetLatitude - $latitude);
        $longitudeDelta = deg2rad($targetLongitude - $longitude);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($latitude)) * cos(deg2rad($targetLatitude)) * sin($longitudeDelta / 2) ** 2;

        return 2 * $earthRadius * asin(min(1, sqrt($a))) <= (int) config('kiosk.radius_meters');
    }
}