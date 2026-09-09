<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RetailProductSeeder extends Seeder
{
    public function run(): void
    {
        $companyId = '019f9878-6c5f-7255-932f-79344fb36ce0';

        Company::query()->findOrFail($companyId);

        DB::transaction(function () use ($companyId): void {
            $categories = $this->seedCategories($companyId);
            $brands = $this->seedBrands($companyId);
            $units = $this->seedUnits($companyId);

            foreach ($this->products() as $index => $data) {
                [$name, $category, $brand, $unit, $purchasePrice, $sellingPrice, $minimumStock] = $data;

                Product::query()->updateOrCreate(
                    [
                        'company_id' => $companyId,
                        'sku' => 'RTL-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                    ],
                    [
                        'category_id' => $categories[$category],
                        'brand_id' => $brands[$brand],
                        'unit_id' => $units[$unit],
                        'name' => $name,
                        'barcode' => '899900000' . str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                        'purchase_price' => $purchasePrice,
                        'selling_price' => $sellingPrice,
                        'minimum_stock' => $minimumStock,
                        'description' => "Produk retail {$name} untuk kebutuhan penjualan harian.",
                        'is_active' => true,
                    ],
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    private function seedCategories(string $companyId): array
    {
        $ids = [];

        foreach ($this->categories() as $name) {
            $category = ProductCategory::query()->updateOrCreate(
                ['company_id' => $companyId, 'name' => $name],
                ['is_active' => true],
            );
            $ids[$name] = (string) $category->getKey();
        }

        return $ids;
    }

    /**
     * @return array<string, string>
     */
    private function seedBrands(string $companyId): array
    {
        $ids = [];

        foreach ($this->brands() as $name) {
            $brand = ProductBrand::query()->updateOrCreate(
                ['company_id' => $companyId, 'name' => $name],
                ['is_active' => true],
            );
            $ids[$name] = (string) $brand->getKey();
        }

        return $ids;
    }

    /**
     * @return array<string, string>
     */
    private function seedUnits(string $companyId): array
    {
        $ids = [];

        foreach ($this->units() as $code => $name) {
            $unit = Unit::query()->updateOrCreate(
                ['company_id' => $companyId, 'code' => $code],
                ['name' => $name, 'is_active' => true],
            );
            $ids[$code] = (string) $unit->getKey();
        }

        return $ids;
    }

    /**
     * @return list<string>
     */
    private function categories(): array
    {
        return [
            'Makanan Ringan',
            'Minuman',
            'Sembako',
            'Produk Instan',
            'Bumbu & Saus',
            'Makanan Kaleng',
            'Susu & Produk Olahan',
            'Roti & Kue',
            'Perawatan Tubuh',
            'Perawatan Rambut',
            'Kebersihan Rumah',
            'Kesehatan',
            'Perlengkapan Bayi',
            'Perawatan Wajah',
            'Kopi & Teh',
        ];
    }

    /**
     * @return list<string>
     */
    private function brands(): array
    {
        return [
            'Indofood',
            'Wings',
            'Unilever',
            'Mayora',
            'Garudafood',
            'Nestle',
            'Danone-AQUA',
            'Coca-Cola',
            'Kapal Api',
            'ABC',
            'Frisian Flag',
            'Sari Roti',
            'Reckitt',
            'Kino',
            'Orang Tua',
            'Ultra Jaya',
            'Sania',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function units(): array
    {
        return [
            'PCS' => 'Pcs',
            'PACK' => 'Pack',
            'BTL' => 'Botol',
            'CAN' => 'Kaleng',
            'BOX' => 'Dus',
        ];
    }

    /**
     * @return list<array{string, string, string, string, int, int, int}>
     */
    private function products(): array
    {
        $products = [
            ['Chitato Sapi Panggang 68g', 'Makanan Ringan', 'Indofood', 'PACK', 8500, 10500, 12],
            ['Qtela Singkong Original 60g', 'Makanan Ringan', 'Indofood', 'PACK', 6500, 8500, 12],
            ['Garuda Kacang Kulit 200g', 'Makanan Ringan', 'Garudafood', 'PACK', 15000, 18500, 10],
            ['Gery Chocolatos Wafer Roll 24g', 'Makanan Ringan', 'Garudafood', 'PACK', 1800, 2500, 24],
            ['Roma Malkist Crackers 250g', 'Makanan Ringan', 'Mayora', 'PACK', 10500, 13500, 10],
            ['Teh Pucuk Harum 350ml', 'Minuman', 'Mayora', 'BTL', 3200, 4500, 24],
            ['AQUA Air Mineral 600ml', 'Minuman', 'Danone-AQUA', 'BTL', 2800, 4000, 24],
            ['Coca-Cola Original 390ml', 'Minuman', 'Coca-Cola', 'BTL', 4800, 6500, 18],
            ['Ultra Milk UHT Cokelat 250ml', 'Susu & Produk Olahan', 'Ultra Jaya', 'BOX', 5500, 7500, 12],
            ['Bear Brand Susu Steril 189ml', 'Susu & Produk Olahan', 'Nestle', 'CAN', 9000, 11500, 12],
            ['Indomie Goreng 85g', 'Produk Instan', 'Indofood', 'PACK', 2600, 3500, 40],
            ['Indomie Soto Mie 70g', 'Produk Instan', 'Indofood', 'PACK', 2500, 3500, 40],
            ['Super Bubur Ayam 45g', 'Produk Instan', 'Indofood', 'PACK', 4200, 5500, 18],
            ['Pop Mie Ayam 75g', 'Produk Instan', 'Indofood', 'PCS', 5000, 7000, 12],
            ['Sania Beras Premium 5kg', 'Sembako', 'Sania', 'PACK', 69000, 76000, 5],
            ['Sania Minyak Goreng 2L', 'Sembako', 'Sania', 'BTL', 33000, 37500, 8],
            ['Sania Tepung Terigu 1kg', 'Sembako', 'Sania', 'PACK', 10500, 13000, 10],
            ['Bimoli Minyak Goreng 1L', 'Sembako', 'Indofood', 'BTL', 17000, 19500, 10],
            ['ABC Kecap Manis 275ml', 'Bumbu & Saus', 'ABC', 'BTL', 10500, 13000, 10],
            ['ABC Saus Sambal 335ml', 'Bumbu & Saus', 'ABC', 'BTL', 11500, 14500, 10],
            ['Indofood Sambal Pedas 275ml', 'Bumbu & Saus', 'Indofood', 'BTL', 10000, 13000, 10],
            ['Indofood Bumbu Racik Ayam Goreng 26g', 'Bumbu & Saus', 'Indofood', 'PACK', 2200, 3000, 24],
            ['ABC Sardines Tomato 155g', 'Makanan Kaleng', 'ABC', 'CAN', 9000, 11500, 12],
            ['ABC Sardines Extra Pedas 155g', 'Makanan Kaleng', 'ABC', 'CAN', 9500, 12000, 12],
            ['Frisian Flag Susu Kental Manis 370g', 'Susu & Produk Olahan', 'Frisian Flag', 'CAN', 12000, 15000, 10],
            ['Dancow FortiGro Cokelat 400g', 'Susu & Produk Olahan', 'Nestle', 'BOX', 43000, 48500, 6],
            ['Ultra Milk Full Cream 1L', 'Susu & Produk Olahan', 'Ultra Jaya', 'BOX', 17500, 21000, 8],
            ['Sari Roti Tawar Spesial', 'Roti & Kue', 'Sari Roti', 'PACK', 13500, 16000, 8],
            ['Sari Roti Sandwich Cokelat', 'Roti & Kue', 'Sari Roti', 'PACK', 4500, 6000, 12],
            ['Kopiko Coffee Candy 150g', 'Makanan Ringan', 'Mayora', 'PACK', 8500, 11000, 10],
            ['Lifebuoy Sabun Batang 80g', 'Perawatan Tubuh', 'Unilever', 'PCS', 3200, 4500, 18],
            ['Lux Sabun Batang 80g', 'Perawatan Tubuh', 'Unilever', 'PCS', 3300, 4500, 18],
            ['Giv Sabun Mandi 76g', 'Perawatan Tubuh', 'Wings', 'PCS', 2500, 3500, 18],
            ['Nuvo Family Soap 80g', 'Perawatan Tubuh', 'Wings', 'PCS', 2800, 4000, 18],
            ['Sunsilk Shampoo Black Shine 170ml', 'Perawatan Rambut', 'Unilever', 'BTL', 18000, 22000, 8],
            ['Clear Shampoo Anti Ketombe 160ml', 'Perawatan Rambut', 'Unilever', 'BTL', 21000, 25500, 8],
            ['Zinc Shampoo Anti Dandruff 170ml', 'Perawatan Rambut', 'Wings', 'BTL', 16000, 20000, 8],
            ['Mama Lemon Pencuci Piring 680ml', 'Kebersihan Rumah', 'Wings', 'BTL', 11000, 14000, 10],
            ['Sunlight Jeruk Nipis 650ml', 'Kebersihan Rumah', 'Unilever', 'BTL', 12500, 15500, 10],
            ['So Klin Liquid Detergent 800ml', 'Kebersihan Rumah', 'Wings', 'BTL', 16500, 20500, 8],
            ['Dettol Antiseptik 100ml', 'Kesehatan', 'Reckitt', 'BTL', 23500, 28000, 6],
            ['Dettol Hand Sanitizer 50ml', 'Kesehatan', 'Reckitt', 'BTL', 15000, 19000, 8],
            ['Ovale Micellar Cleansing Water 100ml', 'Perawatan Wajah', 'Kino', 'BTL', 17000, 21500, 8],
            ['Ellips Hair Vitamin 6 Capsules', 'Perawatan Rambut', 'Kino', 'PACK', 8500, 11000, 10],
            ['Formula Strong Protector 190g', 'Perawatan Tubuh', 'Orang Tua', 'PCS', 11500, 14500, 10],
            ['Tango Wafer Chocolate 130g', 'Makanan Ringan', 'Orang Tua', 'PACK', 7500, 9500, 12],
            ['Cerelac Bubur Bayi Beras Merah 120g', 'Perlengkapan Bayi', 'Nestle', 'BOX', 18000, 22500, 8],
            ['Dancow 1+ Madu 800g', 'Perlengkapan Bayi', 'Nestle', 'BOX', 82000, 91500, 4],
            ['Kapal Api Special Mix 10 Sachet', 'Kopi & Teh', 'Kapal Api', 'PACK', 12000, 15000, 10],
            ['Good Day Cappuccino 10 Sachet', 'Kopi & Teh', 'Kapal Api', 'BOX', 17500, 21500, 8],
        ];

        return array_merge($products, $this->buildAdditionalProducts(200));
    }

    /**
     * @return list<array{string, string, string, string, int, int, int}>
     */
    private function buildAdditionalProducts(int $count): array
    {
        $categories = $this->categories();
        $brands = $this->brands();
        $units = array_keys($this->units());
        $prefixes = ['Fresh', 'Premium', 'Classic', 'Deluxe', 'Eco', 'Daily', 'Special', 'Ultra', 'Smart', 'Modern'];
        $suffixes = ['Mix', 'Baru', 'Jaya', 'Ceria', 'Laris', 'Sehat', 'Berkualitas', 'Mantap', 'Rasa', 'Hemat'];

        $generated = [];

        for ($i = 1; $i <= $count; $i++) {
            $prefix = $prefixes[($i - 1) % count($prefixes)];
            $suffix = $suffixes[($i - 1) % count($suffixes)];
            $category = $categories[($i - 1) % count($categories)];
            $brand = $brands[($i - 1) % count($brands)];
            $unit = $units[($i - 1) % count($units)];
            $purchasePrice = 1500 + (($i % 29) * 750);
            $sellingPrice = $purchasePrice + (500 + (($i % 13) * 250));
            $minimumStock = 4 + ($i % 15);

            $generated[] = [
                sprintf('%s %s %02d', $prefix, $suffix, $i),
                $category,
                $brand,
                $unit,
                $purchasePrice,
                $sellingPrice,
                $minimumStock,
            ];
        }

        return $generated;
    }
}
