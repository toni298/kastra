<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi user "kasir murni" (punya cashier.access, bukan owner)
 * agar hanya bisa mengakses UI kasir. Route lain (dashboard, laporan,
 * pengaturan, dsb.) dipaksa kembali ke halaman kasir.
 *
 * Route yang tetap diizinkan: area cashier/*, profile (ganti password),
 * logout, dan endpoint verifikasi email.
 */
class RedirectCashierToCashier
{
    /**
     * Prefix route/path yang boleh diakses kasir murni.
     * Mencakup UI kasir + endpoint API yang dipakai layar kasir.
     * Route berat (dashboard, laporan, pengaturan, dll.) tetap terblokir
     * karena middleware permission per-route (can:...) menolaknya.
     */
    private const ALLOWED_PREFIXES = [
        'cashier',
        'sales',      // transaksi, pembayaran, pelanggan, cetak struk
        'customers',
        'products',   // katalog produk kasir
        'cash-bank',  // akun kas/bank tujuan pembayaran
        'reports',    // laporan yang diizinkan oleh permission laporan.view
        'profile',
        'logout',
        'verification',
        'password.confirm',
        'sanctum',
        'livewire',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->isCashierOnly()) {
            return $next($request);
        }

        if ($this->isAllowed($request)) {
            return $next($request);
        }

        return redirect()->route('cashier');
    }

    private function isAllowed(Request $request): bool
    {
        $name = $request->route()?->getName() ?? '';
        $path = ltrim($request->path(), '/');

        foreach (self::ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($name, $prefix) || str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
