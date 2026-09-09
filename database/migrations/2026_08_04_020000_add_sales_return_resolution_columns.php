<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sales_returns', function (Blueprint $table) {
            $table->enum('resolution', ['refund', 'ganti_produk', 'potong_tagihan'])->default('refund')->after('reason');
            $table->unsignedBigInteger('refund_amount')->default(0)->after('total');
            $table->unsignedBigInteger('replacement_total')->default(0)->after('refund_amount');
            $table->unsignedBigInteger('customer_credit_amount')->default(0)->after('replacement_total');
            $table->unsignedBigInteger('customer_pays_amount')->default(0)->after('customer_credit_amount');
            $table->index(['resolution']);
        });

        Schema::create('sales_return_replacements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sales_return_id')->constrained('sales_returns')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->restrictOnDelete();
            $table->unsignedBigInteger('quantity');
            $table->unsignedBigInteger('unit_price');
            $table->unsignedBigInteger('subtotal');
            $table->timestamps();
            $table->unique(['sales_return_id', 'product_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->bigInteger('credit_balance')->default(0)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('credit_balance');
        });

        Schema::dropIfExists('sales_return_replacements');

        Schema::table('sales_returns', function (Blueprint $table) {
            $table->dropIndex(['resolution']);
            $table->dropColumn(['resolution', 'refund_amount', 'replacement_total', 'customer_credit_amount', 'customer_pays_amount']);
        });
    }
};
