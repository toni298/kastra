<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Active master-data dropdowns: WHERE company_id + is_active ORDER BY name.
        Schema::table('gudang', function (Blueprint $table) {
            $table->index(['company_id', 'aktif', 'nama'], 'gudang_company_active_name_idx');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->index(['company_id', 'is_active', 'name'], 'product_category_company_active_name_idx');
        });

        Schema::table('product_brands', function (Blueprint $table) {
            $table->index(['company_id', 'is_active', 'name'], 'product_brand_company_active_name_idx');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->index(['company_id', 'is_active', 'name'], 'unit_company_active_name_idx');
        });

        // Product search/select endpoints scope by tenant and active state, then sort by name/id.
        Schema::table('products', function (Blueprint $table) {
            $table->index(['company_id', 'is_active', 'name', 'id'], 'products_company_active_name_id_idx');
        });

        // ProductStockRepository defaults to tenant-scoped newest-first pagination.
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->index(['company_id', 'created_at', 'id'], 'product_stocks_company_created_id_idx');
        });

        // Product::images() filters by product and orders by sort_order.
        Schema::table('product_images', function (Blueprint $table) {
            $table->index(['product_id', 'sort_order'], 'product_images_product_sort_idx');
        });

        // TransferTransactionRepository: tenant-scoped newest-first pagination.
        Schema::table('transfer_transactions', function (Blueprint $table) {
            $table->index(['company_id', 'transfer_date', 'id'], 'transfer_company_date_id_idx');
        });

        // Stock opname list: tenant/status/date pagination, plus location filters.
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->index(['company_id', 'gudang_id', 'created_at'], 'opname_company_gudang_created_idx');
            $table->index(['company_id', 'branch_id', 'created_at'], 'opname_company_branch_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropIndex('opname_company_branch_created_idx');
            $table->dropIndex('opname_company_gudang_created_idx');
        });

        Schema::table('transfer_transactions', function (Blueprint $table) {
            $table->dropIndex('transfer_company_date_id_idx');
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->dropIndex('product_images_product_sort_idx');
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropIndex('product_stocks_company_created_id_idx');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_company_active_name_id_idx');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropIndex('unit_company_active_name_idx');
        });

        Schema::table('product_brands', function (Blueprint $table) {
            $table->dropIndex('product_brand_company_active_name_idx');
        });

        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropIndex('product_category_company_active_name_idx');
        });

        Schema::table('gudang', function (Blueprint $table) {
            $table->dropIndex('gudang_company_active_name_idx');
        });
    }
};
