<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('sales_transaction_id')->constrained('sales_transactions')->restrictOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->string('return_number', 50);
            $table->enum('reason', ['damaged', 'wrong_delivery', 'not_as_ordered', 'expired', 'other']);
            $table->enum('status', ['draft', 'completed'])->default('draft');
            $table->text('note')->nullable();
            $table->unsignedBigInteger('total')->default(0);
            $table->timestamps();
            $table->unique(['company_id', 'return_number']);
            $table->index(['company_id', 'sales_transaction_id']);
        });

        Schema::create('sales_return_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sales_return_id')->constrained('sales_returns')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
            $table->unique(['sales_return_id', 'product_id']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignUuid('reference_id')->nullable();
            $table->string('reference_type')->nullable();
            $table->enum('movement_type', ['in', 'out', 'adjustment']);
            $table->bigInteger('quantity');
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'branch_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('sales_return_details');
        Schema::dropIfExists('sales_returns');
    }
};
