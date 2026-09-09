<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->enum('source_type', ['gudang', 'cabang'])->default('gudang')->after('company_id');
            $table->foreignUuid('gudang_id')->nullable()->change();
            $table->foreignUuid('branch_id')->nullable()->after('gudang_id')->constrained('branches')->nullOnDelete();
        });

        Schema::table('stock_opname_details', function (Blueprint $table) {
            $table->foreignUuid('product_stock_id')->nullable()->change();
            $table->foreignUuid('branch_product_stock_id')->nullable()->after('product_stock_id')->constrained('branch_product_stocks')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_opname_details', function (Blueprint $table) {
            $table->dropForeign(['branch_product_stock_id']);
            $table->dropColumn('branch_product_stock_id');
            $table->foreignUuid('product_stock_id')->nullable(false)->change();
        });

        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['branch_id', 'source_type']);
            $table->foreignUuid('gudang_id')->nullable(false)->change();
        });
    }
};
