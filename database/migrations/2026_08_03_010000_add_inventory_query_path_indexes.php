<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// ponytail: index ditambah berdasarkan match pola query di repository (orderBy + whereColumn subquery).
// EXPLAIN pada DB nyata belum dijalankan (AOQ 109). Upgrade path: jalankan EXPLAIN sebelum deploy produksi;
// hapus index yang tidak terpakai oleh query planner.

return new class extends Migration
{
    public function up(): void
    {
        // StockOpnameRepository default orderBy: latest('opname_date')->latest('id') scoped by company_id.
        // Index existing (company_id, gudang_id/branch_id, created_at) tidak match orderBy opname_date.
        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->index(['company_id', 'opname_date', 'id'], 'opname_company_date_id_idx');
        });

        // StockOpnameRepository::paginate selectSub korelasi:
        // WHERE stock_opname_id = stock_opnames.id AND physical_quantity IS NOT NULL.
        // Tanpa index ini, subquery full-scan per baris opname saat list tumbuh.
        Schema::table('stock_opname_details', function (Blueprint $table) {
            $table->index(['stock_opname_id', 'physical_quantity'], 'opname_detail_opname_physical_idx');
        });
    }

    public function down(): void
    {
        Schema::table('stock_opname_details', function (Blueprint $table) {
            $table->dropIndex('opname_detail_opname_physical_idx');
        });

        Schema::table('stock_opnames', function (Blueprint $table) {
            $table->dropIndex('opname_company_date_id_idx');
        });
    }
};
