<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('gudang_id')->constrained('gudang')->restrictOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('completed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('opname_number', 50);
            $table->date('opname_date');
            $table->enum('status', ['draft', 'in_progress', 'completed', 'cancelled'])->default('draft');
            $table->text('note')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'opname_number']);
            $table->index(['company_id', 'status', 'opname_date'], 'stock_opname_scope_idx');
        });

        Schema::create('stock_opname_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('stock_opname_id')->constrained('stock_opnames')->cascadeOnDelete();
            $table->foreignUuid('product_stock_id')->constrained('product_stocks')->restrictOnDelete();
            $table->unsignedBigInteger('system_quantity');
            $table->unsignedBigInteger('physical_quantity')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['stock_opname_id', 'product_stock_id'], 'stock_opname_detail_unique');
            $table->index(['product_stock_id', 'created_at'], 'stock_opname_stock_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opname_details');
        Schema::dropIfExists('stock_opnames');
    }
};
