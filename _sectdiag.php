<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Branch;
use Illuminate\Support\Facades\DB;

$companySlug = $argv[1] ?? 'instinct-dev';
$branchSlug = $argv[2] ?? $companySlug;

$store = Branch::query()
    ->join('companies', 'companies.id', '=', 'branches.company_id')
    ->where('companies.slug', $companySlug)
    ->where('branches.slug', $branchSlug)
    ->select('branches.*')
    ->first();

if (!$store) { echo "store tidak ada\n"; exit; }

echo "Store: {$store->name} (id={$store->id})\n\n";

// Settings & sections
$s = $store->company->storeSetting;
if ($s) {
    echo "=== Sections (active) ===\n";
    foreach ($s->active_sections as $sec) {
        echo "  - {$sec['id']} | {$sec['label']} | enabled=" . var_export($sec['enabled'], true) . "\n";
    }
    echo "\n=== Sections (all, dari meta) ===\n";
    foreach ($s->sections as $sec) {
        echo "  - {$sec['id']} | enabled=" . var_export($sec['enabled'], true) . " | sort={$sec['sort']}\n";
    }
} else {
    echo "❌ storeSetting BELUM ADA -> sections fallback ke default (semua aktif)\n";
}

// Produk di branch ini
echo "\n=== Stok produk branch ini ===\n";
$totalProducts = DB::table('products')->where('company_id', $store->company_id)->where('is_active', true)->count();
echo "Total produk aktif company: {$totalProducts}\n";

$stocked = DB::table('branch_product_stocks')
    ->where('branch_id', $store->id)
    ->where('quantity', '>', 0)
    ->count();
echo "Produk dengan stok >0 di branch ini: {$stocked}\n";

$withDiscount = DB::table('branch_product_stocks')
    ->where('branch_id', $store->id)
    ->where('quantity', '>', 0)
    ->where('discount', '>', 0)
    ->count();
echo "Produk dengan diskon >0 di branch ini: {$withDiscount}\n";

// Simulasi query catalogBaseQuery (latest)
$base = DB::table('products')
    ->join('branch_product_stocks', function ($join) use ($store) {
        $join->on('branch_product_stocks.product_id', '=', 'products.id')
            ->where('branch_product_stocks.branch_id', $store->id)
            ->where('branch_product_stocks.quantity', '>', 0);
    })
    ->where('products.company_id', $store->company_id)
    ->where('products.is_active', true);

$latestCount = (clone $base)->count();
$promoCount = (clone $base)->where('branch_product_stocks.discount', '>', 0)->count();

echo "\n=== Hasil query storefront ===\n";
echo "latest (produk tampil): {$latestCount}\n";
echo "promos (diskon>0): {$promoCount}\n";

echo "\n==> Diagnosis:\n";
if ($latestCount === 0) echo "  - LATEST kosong: tidak ada produk aktif dengan stok>0 di branch ini\n";
if ($promoCount === 0) echo "  - PROMOS kosong: tidak ada produk dengan discount>0 di branch ini\n";
if ($latestCount > 0 && $promoCount === 0) echo "  - Promo section akan hidden karena promos.length===0 (v-if='activeSections.promos && promos.length')\n";
