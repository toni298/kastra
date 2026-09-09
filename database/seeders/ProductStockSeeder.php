<?php

namespace Database\Seeders;

use App\Models\Gudang;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductStockSeeder extends Seeder
{
    private const STOCK_COUNT = 1000;

    public function run(): void
    {
        $products = Product::query()
            ->orderBy('company_id')
            ->orderBy('id')
            ->get(['id', 'company_id']);

        if ($products->isEmpty()) {
            throw new RuntimeException('Produk belum tersedia. Jalankan RetailProductSeeder terlebih dahulu.');
        }

        DB::transaction(function () use ($products): void {
            $remaining = self::STOCK_COUNT;

            foreach ($products->groupBy('company_id') as $companyId => $companyProducts) {
                if ($remaining === 0) {
                    break;
                }

                $stockCount = min($remaining, self::STOCK_COUNT);
                $warehouses = $this->seedWarehouses((string) $companyId, $companyProducts, $stockCount);

                foreach ($warehouses as $warehouseIndex => $warehouse) {
                    foreach ($companyProducts as $productIndex => $product) {
                        if ($remaining === 0) {
                            break 2;
                        }

                        ProductStock::query()->updateOrCreate(
                            [
                                'company_id' => $companyId,
                                'product_id' => $product->id,
                                'gudang_id' => $warehouse->id,
                            ],
                            [
                                'quantity' => (($productIndex + 1) * 7 + ($warehouseIndex + 1) * 13) % 501,
                            ],
                        );

                        $remaining--;
                    }
                }
            }

            if ($remaining > 0) {
                throw new RuntimeException("Gagal membuat 1.000 data stok. Masih kurang {$remaining} kombinasi.");
            }
        });
    }

    /**
     * @param  Collection<int, Product>  $products
     * @return Collection<int, Gudang>
     */
    private function seedWarehouses(string $companyId, Collection $products, int $stockCount): Collection
    {
        $warehouseCount = (int) ceil($stockCount / $products->count());
        $warehouses = collect();

        for ($index = 1; $index <= $warehouseCount; $index++) {
            $warehouses->push(Gudang::query()->updateOrCreate(
                [
                    'company_id' => $companyId,
                    'kode' => 'SEED-STOCK-'.str_pad((string) $index, 3, '0', STR_PAD_LEFT),
                ],
                [
                    'nama' => "Gudang Stok Demo {$index}",
                    'alamat' => "Lokasi gudang stok demo {$index}",
                    'aktif' => true,
                ],
            ));
        }

        return $warehouses;
    }
}
