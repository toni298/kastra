<?php

namespace App\Services;

use App\Jobs\ProcessProductImage;
use App\Models\Branch;
use App\Models\BranchProductStock;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    public function suggestions(string $companyId): array
    {
        return [
            'sku' => $this->uniqueSku($companyId),
            'barcode' => $this->uniqueBarcode($companyId),
        ];
    }

    public function create(string $companyId, string $userId, array $data): Product
    {
        return DB::transaction(function () use ($companyId, $userId, $data) {
            $product = Product::create([
                ...Arr::except($data, ['branch_stocks', 'image_ids', 'images']),
                'company_id' => $companyId,
            ]);

            $this->syncImages($product, $data['image_ids'] ?? []);
            $this->storeImages($product, $userId, $data['images'] ?? []);

            $branchStocks = $data['branch_stocks'] ?? [];
            if (empty($branchStocks)) {
                $branches = Branch::query()
                    ->where('company_id', $companyId)
                    ->where('status', Branch::STATUS_ACTIVE)
                    ->get(['id']);
                $branchStocks = $branches->map(fn ($branch) => [
                    'branch_id' => $branch->id,
                    'quantity' => 0,
                ])->all();
            }
            foreach ($branchStocks as $stock) {
                BranchProductStock::create([
                    'company_id' => $companyId,
                    'branch_id' => $stock['branch_id'],
                    'product_id' => $product->id,
                    'quantity' => (int) ($stock['quantity'] ?? 0),
                ]);
            }

            return $product;
        });
    }

    public function update(Product $product, string $userId, array $data): Product
    {
        return DB::transaction(function () use ($product, $userId, $data) {
            $product->update(Arr::except($data, ['branch_stocks', 'image_ids', 'images']));
            $this->syncImages($product, $data['image_ids'] ?? []);
            $this->storeImages($product, $userId, $data['images'] ?? []);

            return $product->refresh();
        });
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {
            foreach ($product->images as $image) {
                $this->deleteImageFiles($image);
            }

            $product->delete();
        });
    }

    private function syncImages(Product $product, array $imageIds): void
    {
        $removed = $product->images()->whereNotIn('id', $imageIds)->get();

        foreach ($removed as $image) {
            $this->deleteImageFiles($image);
            $image->delete();
        }

        ProductImage::query()
            ->where('company_id', $product->company_id)
            ->whereIn('id', $imageIds)
            ->where(fn ($query) => $query
                ->whereNull('product_id')
                ->orWhere('product_id', $product->id))
            ->update(['product_id' => $product->id]);
    }

    /**
     * @param  list<UploadedFile>  $images
     */
    private function storeImages(Product $product, string $userId, array $images): void
    {
        foreach ($images as $image) {
            $path = $image->storeAs(
                "products/{$product->company_id}/original",
                Str::uuid().'.'.$image->extension(),
                'public',
            );

            abort_unless($path, 500, 'Foto produk gagal disimpan.');

            $productImage = $product->images()->create([
                'company_id' => $product->company_id,
                'uploaded_by' => $userId,
                'original_path' => $path,
                'status' => 'pending',
            ]);

            ProcessProductImage::dispatch($productImage->id)->afterCommit();
        }
    }

    private function deleteImageFiles(ProductImage $image): void
    {
        foreach (['original_path', 'webp_path', 'thumbnail_path'] as $attribute) {
            if ($image->{$attribute}) {
                Storage::disk('public')->delete($image->{$attribute});
            }
        }
    }

    private function uniqueSku(string $companyId): string
    {
        do {
            $sku = 'SKU-'.strtoupper(Str::random(8));
        } while (Product::query()->where('company_id', $companyId)->where('sku', $sku)->exists());

        return $sku;
    }

    private function uniqueBarcode(string $companyId): string
    {
        do {
            $barcode = (string) random_int(1000000000000, 9999999999999);
        } while (Product::query()->where('company_id', $companyId)->where('barcode', $barcode)->exists());

        return $barcode;
    }
}
