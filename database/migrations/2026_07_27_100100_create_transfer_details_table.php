<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_details', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('transfer_transaction_id')->constrained('transfer_transactions')->cascadeOnDelete();
            $table->foreignUuid('source_stock_id')->constrained('product_stocks')->restrictOnDelete();
            $table->foreignUuid('destination_stock_id')->constrained('product_stocks')->restrictOnDelete();
            $table->unsignedBigInteger('quantity');
            $table->timestamps();

            $table->unique(['transfer_transaction_id', 'source_stock_id']);
            $table->index(['source_stock_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_details');
    }
};
