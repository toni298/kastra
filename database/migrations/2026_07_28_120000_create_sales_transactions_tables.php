<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->string('transaction_number', 50);
            $table->string('customer_name')->nullable();
            $table->string('document_type', 30)->default('invoice');
            $table->date('transaction_date');
            $table->enum('status', ['draft', 'completed', 'return', 'cancelled'])->default('completed');
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid');
            $table->unsignedBigInteger('total')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'transaction_number']);
            $table->index(['company_id', 'branch_id', 'transaction_date']);
        });

        Schema::create('sales_transaction_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sales_transaction_id')->constrained('sales_transactions')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();

            $table->unique(['sales_transaction_id', 'product_id']);
            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_transaction_details');
        Schema::dropIfExists('sales_transactions');
    }
};
