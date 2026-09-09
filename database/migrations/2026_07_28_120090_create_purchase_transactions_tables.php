<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignUuid('gudang_id')->nullable()->constrained('gudang')->nullOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->string('transaction_number', 60);
            $table->enum('document_type', ['purchase_order', 'purchase_invoice']);
            $table->enum('status', ['draft', 'ordered', 'received', 'completed', 'cancelled', 'returned'])->default('draft');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->date('transaction_date');
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('tax')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'transaction_number']);
            $table->index(['company_id', 'status', 'transaction_date']);
        });
        Schema::create('purchase_transaction_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_transaction_id')->constrained('purchase_transactions')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
            $table->unique(
                ['purchase_transaction_id', 'product_id'],
                'purchase_detail_product_unique'
            );
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('purchase_transaction_details');
        Schema::dropIfExists('purchase_transactions');
    }
};
