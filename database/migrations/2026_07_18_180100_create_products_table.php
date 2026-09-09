<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
   public function up(): void
   {
      Schema::create('products', function (Blueprint $table) {
         $table->uuid('id')->primary();
         $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
         $table->foreignUuid('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
         $table->foreignUuid('brand_id')->nullable()->constrained('product_brands')->nullOnDelete();
         $table->foreignUuid('unit_id')->constrained('units')->restrictOnDelete();
         $table->string('nama');
         $table->string('sku', 80);
         $table->string('barcode', 100)->nullable();
         $table->unsignedBigInteger('harga_beli')->default(0);
         $table->unsignedBigInteger('harga_jual')->default(0);
         $table->unsignedBigInteger('minimum_stok')->default(0);
         $table->longText('deskripsi')->nullable();
         $table->boolean('aktif')->default(true);
         $table->timestamps();
         $table->unique(['company_id', 'sku']);
         $table->unique(['company_id', 'barcode']);
         $table->index(['company_id', 'created_at']);
      });
      Schema::create('product_images', function (Blueprint $table) {
         $table->uuid('id')->primary();
         $table->foreignUuid('product_id')->nullable()->constrained('products')->cascadeOnDelete();
         $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
         $table->foreignUuid('uploaded_by')->constrained('users')->cascadeOnDelete();
         $table->string('original_path');
         $table->string('webp_path')->nullable();
         $table->string('thumbnail_path')->nullable();
         $table->enum('status', ['pending', 'processing', 'ready', 'failed'])->default('pending');
         $table->unsignedInteger('sort_order')->default(0);
         $table->timestamps();
         $table->index(['company_id', 'status']);
      });
   }
   public function down(): void
   {
      Schema::dropIfExists('product_images');
      Schema::dropIfExists('products');
   }
};
