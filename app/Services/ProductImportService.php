<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\Branch;
use App\Models\BranchProductStock;
use App\Models\Unit;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\FastExcel;

class ProductImportService
{
    /**
     * @return array{valid_rows: list<array>, invalid_rows: list<array>, total: int}
     */
    public function preview(string $companyId, UploadedFile $file): array
    {
        $rows = $this->readRows($file);
            $normalizedRows = array_map(fn (array $row) => $this->normalizeRow($row), $rows);
            $skusInFile = array_values(array_filter(array_map(fn (array $row) => trim((string) ($row['sku'] ?? '')), $normalizedRows)));
            $barcodesInFile = array_values(array_filter(array_map(fn (array $row) => trim((string) ($row['barcode'] ?? '')), $normalizedRows)));

            $existingSkus = array_fill_keys(Product::query()
                ->where('company_id', $companyId)
                ->whereIn('sku', $skusInFile)
                ->pluck('sku')
                ->all(), true);
            $existingBarcodes = array_fill_keys(Product::query()
                ->where('company_id', $companyId)
                ->whereNotNull('barcode')
                ->whereIn('barcode', $barcodesInFile)
                ->pluck('barcode')
                ->all(), true);

            $categories = ProductCategory::query()->where('company_id', $companyId)->where('is_active', true)->pluck('id', 'name')
                ->mapWithKeys(fn ($id, $name) => [mb_strtolower(trim((string) $name)) => $id])->all();
            $brands = ProductBrand::query()->where('company_id', $companyId)->where('is_active', true)->pluck('id', 'name')
                ->mapWithKeys(fn ($id, $name) => [mb_strtolower(trim((string) $name)) => $id])->all();
            $units = [];
            foreach (Unit::query()->where('company_id', $companyId)->where('is_active', true)->get(['id', 'name', 'code']) as $unit) {
                $units[mb_strtolower(trim((string) $unit->name))] = $unit->id;
                $units[mb_strtolower(trim((string) $unit->code))] = $unit->id;
            }

            $validRows = [];
            $invalidRows = [];
            $seenSkus = [];
            $seenBarcodes = [];

            foreach ($normalizedRows as $index => $data) {
                $lineNumber = $index + 2;
                $errors = [];
                $name = trim((string) ($data['name'] ?? ''));
                $sku = trim((string) ($data['sku'] ?? ''));
                $barcode = trim((string) ($data['barcode'] ?? ''));
                $unitName = trim((string) ($data['unit'] ?? ''));
                $purchasePrice = $data['purchase_price'] ?? null;
                $sellingPrice = $data['selling_price'] ?? null;
                $minimumStock = $data['minimum_stock'] ?? null;
                $initialStock = $data['initial_stock'] ?? 0;

                if ($name === '') $errors[] = 'Nama produk wajib diisi';
                if ($sku === '') {
                    $errors[] = 'SKU wajib diisi';
                } else {
                    if (isset($existingSkus[$sku])) $errors[] = 'SKU sudah terdaftar di database';
                    if (isset($seenSkus[$sku])) $errors[] = 'SKU duplikat dalam file';
                    $seenSkus[$sku] = true;
                }
                if ($barcode !== '') {
                    if (isset($existingBarcodes[$barcode])) $errors[] = 'Barcode sudah terdaftar di database';
                    if (isset($seenBarcodes[$barcode])) $errors[] = 'Barcode duplikat dalam file';
                    $seenBarcodes[$barcode] = true;
                }

                $unitName = $unitName !== '' ? $unitName : 'PCS';
                $unitId = $units[mb_strtolower($unitName)] ?? null;

                $categoryName = trim((string) ($data['category'] ?? ''));
                $categoryId = $categoryName === '' ? null : ($categories[mb_strtolower($categoryName)] ?? null);
                $brandName = trim((string) ($data['brand'] ?? ''));
                $brandId = $brandName === '' ? null : ($brands[mb_strtolower($brandName)] ?? null);

                if ($purchasePrice === null || ! is_numeric($purchasePrice) || (float) $purchasePrice < 0
                    || $sellingPrice === null || ! is_numeric($sellingPrice) || (float) $sellingPrice < 0) {
                    $errors[] = 'Harga harus berupa angka positif';
                }
                if ($minimumStock === null || ! is_numeric($minimumStock) || (float) $minimumStock < 0) {
                    $errors[] = 'Minimum stok harus berupa angka positif';
                }
                if (! is_numeric($initialStock) || (float) $initialStock < 0) {
                    $errors[] = 'Stok awal harus berupa angka non-negatif';
                }

                $payload = [
                    'name' => $name, 'sku' => $sku, 'barcode' => $barcode !== '' ? $barcode : null,
                    'category' => $categoryName ?: null,
                    'brand' => $brandName ?: null,
                    'unit' => $unitName,
                    'category_id' => $categoryId, 'brand_id' => $brandId, 'unit_id' => $unitId,
                    'purchase_price' => (int) $purchasePrice, 'selling_price' => (int) $sellingPrice,
                    'minimum_stock' => (int) $minimumStock,
                    'initial_stock' => (int) $initialStock,
                    'description' => trim((string) ($data['description'] ?? '')) ?: null,
                ];
                $result = ['row_number' => $lineNumber, 'status' => $errors === [] ? 'valid' : 'invalid', 'row' => $data];
                if ($errors === []) {
                    $validRows[] = [...$result, 'data' => $payload];
                } else {
                    $invalidRows[] = [...$result, 'errors' => $errors];
                }
            }

            return ['valid_rows' => $validRows, 'invalid_rows' => $invalidRows, 'total' => count($normalizedRows)];
        }

    public function commitChunk(string $companyId, array $rows): int
    {
        if ($rows === []) {
            return 0;
        }

        $categoryMap = [];
        $brandMap = [];
        $unitMap = [];
        $payload = [];
        $stockQuantityByProduct = [];

        foreach ($rows as $row) {
            $categoryName = trim((string) ($row['category'] ?? ''));
            $categoryKey = mb_strtolower($categoryName);
            $categoryId = null;
            if ($categoryName !== '') {
                if (! isset($categoryMap[$categoryKey])) {
                    $categoryMap[$categoryKey] = ProductCategory::firstOrCreate(
                        ['company_id' => $companyId, 'name' => $categoryName],
                        ['is_active' => true],
                    )->id;
                }
                $categoryId = $categoryMap[$categoryKey];
            }

            $brandName = trim((string) ($row['brand'] ?? ''));
            $brandKey = mb_strtolower($brandName);
            $brandId = null;
            if ($brandName !== '') {
                if (! isset($brandMap[$brandKey])) {
                    $brandMap[$brandKey] = ProductBrand::firstOrCreate(
                        ['company_id' => $companyId, 'name' => $brandName],
                        ['is_active' => true],
                    )->id;
                }
                $brandId = $brandMap[$brandKey];
            }

            $unitName = trim((string) ($row['unit'] ?? '')) ?: 'PCS';
            $unitKey = mb_strtolower($unitName);
            if (! isset($unitMap[$unitKey])) {
                $unitMap[$unitKey] = Unit::firstOrCreate(
                    ['company_id' => $companyId, 'name' => $unitName],
                    [
                        'code' => strtoupper(Str::slug($unitName)),
                        'is_active' => true,
                    ],
                )->id;
            }

            $productId = (string) Str::uuid();
            $payload[] = [
                'id' => $productId,
                'company_id' => $companyId,
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'unit_id' => $unitMap[$unitKey],
                'name' => trim((string) ($row['name'] ?? '')),
                'sku' => trim((string) ($row['sku'] ?? '')),
                'barcode' => trim((string) (($row['barcode'] ?? '')) ?: '') !== '' ? trim((string) ($row['barcode'] ?? '')) : null,
                'purchase_price' => (int) ($row['purchase_price'] ?? 0),
                'selling_price' => (int) ($row['selling_price'] ?? 0),
                'minimum_stock' => (int) ($row['minimum_stock'] ?? 0),
                'description' => trim((string) ($row['description'] ?? '')) !== '' ? trim((string) ($row['description'] ?? '')) : null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $stockQuantityByProduct[$productId] = max(0, (int) ($row['initial_stock'] ?? 0));
        }

        DB::transaction(function () use ($companyId, $payload, $stockQuantityByProduct) {
            Product::query()->insert($payload);

            $branches = Branch::query()
                ->where('company_id', $companyId)
                ->where('status', Branch::STATUS_ACTIVE)
                ->get(['id']);
            $stockPayload = [];

            foreach ($payload as $product) {
                foreach ($branches as $branch) {
                    $stockPayload[] = [
                        'id' => (string) Str::uuid(),
                        'company_id' => $companyId,
                        'branch_id' => $branch->id,
                        'product_id' => $product['id'],
                        'quantity' => $stockQuantityByProduct[$product['id']] ?? 0,
                        'discount' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if ($stockPayload !== []) {
                BranchProductStock::query()->insert($stockPayload);
            }
        });

        return count($payload);
    }

    public function downloadTemplate()
    {
        try {
            if (! extension_loaded('zip')) {
                throw new \RuntimeException('Ekstensi PHP "ext-zip" belum aktif.');
            }

            $data = collect([
                [
                    'nama_produk' => 'Sabun Mandi Wangi 100g',
                    'sku_kode_produk' => 'SBM-001',
                    'barcode' => '8991234567890',
                    'kategori' => 'Perawatan Diri',
                    'merek' => 'Glow',
                    'satuan' => 'PCS',
                    'harga_beli' => 15000,
                    'harga_jual' => 22000,
                    'stok_minimal' => 10,
                    'stok_awal' => 50,
                    'deskripsi' => 'Sabun mandi ekstrak bunga',
                ],
            ]);

            $fileName = 'Template_Import_Produk_'.time().'.xlsx';
            $filePath = storage_path('app/'.$fileName);

            (new FastExcel($data))->export($filePath);

            return response()->download($filePath, 'Template_Import_Produk.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ])->deleteFileAfterSend(true);
        } catch (\Throwable $e) {
            Log::error('Template Download Failed', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat file template: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readRows(UploadedFile $file): array
    {
        $path = $file->getRealPath();
        if ($path === false || ! is_file($path)) {
            return [];
        }

            $rows = (new FastExcel)->import($path);

        if ($rows instanceof Collection) {
            return $rows->values()->all();
        }

        if (is_array($rows)) {
            return array_values($rows);
        }

        return [];
    }

    /**
     * @param  array<string, mixed>|list<mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row): array
    {
        $normalized = [];

        foreach ($row as $key => $value) {
            $label = is_string($key) ? strtolower(trim($key)) : (string) $key;
            $normalized[$label] = $value;
        }

        $keys = [
            'name', 'nama', 'nama_produk', 'product_name', 'produk', 'product',
            'sku', 'sku_kode_produk', 'kode', 'product_sku',
            'barcode', 'bar_code', 'kode_barcode',
            'category', 'kategori', 'category_name', 'product_category',
            'brand', 'merek', 'brand_name',
            'unit', 'satuan', 'unit_name', 'unit_code',
            'purchase_price', 'harga_beli', 'buy_price', 'beli',
            'selling_price', 'harga_jual', 'sell_price', 'jual',
            'minimum_stock', 'minimum_stok', 'stok_minimal', 'stok_minimum', 'stok_min',
            'initial_stock', 'stok_awal', 'stok_awal_produk',
            'description', 'deskripsi', 'notes',
        ];

        $mapped = [];
        foreach ($keys as $key) {
            $mapped[$key] = $this->extractValue($normalized, $key);
        }

        $result = [
            'name' => $this->coerceScalar($mapped['name'] ?? $mapped['nama_produk'] ?? $mapped['nama'] ?? $mapped['product_name'] ?? $mapped['produk'] ?? $mapped['product'] ?? ''),
            'sku' => $this->coerceScalar($mapped['sku_kode_produk'] ?? $mapped['sku'] ?? $mapped['kode'] ?? $mapped['product_sku'] ?? ''),
            'barcode' => $this->coerceScalar($mapped['barcode'] ?? $mapped['bar_code'] ?? $mapped['kode_barcode'] ?? ''),
            'category' => $this->coerceScalar($mapped['category'] ?? $mapped['kategori'] ?? $mapped['category_name'] ?? $mapped['product_category'] ?? ''),
            'brand' => $this->coerceScalar($mapped['brand'] ?? $mapped['merek'] ?? $mapped['brand_name'] ?? ''),
            'unit' => $this->coerceScalar($mapped['unit'] ?? $mapped['satuan'] ?? $mapped['unit_name'] ?? $mapped['unit_code'] ?? ''),
            'purchase_price' => $this->normalizeNumber($mapped['purchase_price'] ?? $mapped['harga_beli'] ?? $mapped['buy_price'] ?? $mapped['beli'] ?? null),
            'selling_price' => $this->normalizeNumber($mapped['selling_price'] ?? $mapped['harga_jual'] ?? $mapped['sell_price'] ?? $mapped['jual'] ?? null),
            'minimum_stock' => $this->normalizeNumber($mapped['stok_minimal'] ?? $mapped['minimum_stock'] ?? $mapped['minimum_stok'] ?? $mapped['stok_minimum'] ?? $mapped['stok_min'] ?? null),
            'initial_stock' => $this->normalizeNumber($mapped['stok_awal'] ?? $mapped['initial_stock'] ?? $mapped['stok_awal_produk'] ?? 0) ?? 0,
            'description' => $this->coerceScalar($mapped['description'] ?? $mapped['deskripsi'] ?? $mapped['notes'] ?? ''),
        ];

        return $result;
    }

    private function extractValue(array $row, string $key): mixed
    {
        $search = [
            $key,
            str_replace('_', '', $key),
            str_replace('_', ' ', $key),
            preg_replace('/[^a-z0-9]+/i', '', $key),
        ];

        foreach ($search as $candidate) {
            if (array_key_exists($candidate, $row)) {
                return $row[$candidate];
            }

            foreach ($row as $rowKey => $value) {
                $normalizedKey = strtolower(trim((string) $rowKey));
                if ($normalizedKey === $candidate || $normalizedKey === preg_replace('/[^a-z0-9]+/i', '', $candidate)) {
                    return $value;
                }
            }
        }

        return null;
    }

    private function normalizeNumber(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = preg_replace('/[^0-9,.-]/', '', (string) $value);
        if ($clean === '') {
            return null;
        }

        $clean = str_replace('.', '', $clean);
        $clean = str_replace(',', '.', $clean);

        return is_numeric($clean) ? (float) $clean : null;
    }

    private function parseBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        $text = strtolower(trim((string) $value));

        return in_array($text, ['1', 'true', 'yes', 'y', 'aktif', 'active', 'on'], true);
    }

    private function coerceScalar(mixed $value): string
    {
        if (is_array($value)) {
            return trim((string) json_encode($value));
        }

        return trim((string) ($value ?? ''));
    }

}
