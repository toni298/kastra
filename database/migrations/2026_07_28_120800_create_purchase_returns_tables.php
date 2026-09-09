<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('purchase_transaction_id')->constrained('purchase_transactions')->restrictOnDelete();
            $table->foreignUuid('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignUuid('gudang_id')->nullable()->constrained('gudang')->nullOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->string('return_number', 60);
            $table->enum('reason', ['rusak', 'tidak_sesuai', 'jumlah_lebih', 'kedaluwarsa']);
            $table->enum('resolution', ['penggantian', 'potong_tagihan', 'refund']);
            $table->date('return_date');
            $table->unsignedBigInteger('total')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'return_number'], 'pr_company_number_uq');
            $table->index(['company_id', 'purchase_transaction_id'], 'pr_transaction_idx');
        });
        Schema::create('purchase_return_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_return_id')->constrained('purchase_returns')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
            $table->unique(['purchase_return_id', 'product_id'], 'prd_return_product_uq');
        });
    }
    public function down(): void { Schema::dropIfExists('purchase_return_details'); Schema::dropIfExists('purchase_returns'); }
};
