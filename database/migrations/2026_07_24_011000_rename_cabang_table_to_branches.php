<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });

        Schema::table('outlet', function (Blueprint $table) {
            $table->dropForeign(['cabang_id']);
            $table->dropIndex(['cabang_id', 'created_at']);
        });

        Schema::table('cabang', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropUnique(['company_id', 'kode']);
            $table->dropIndex(['company_id', 'created_at']);
            $table->dropIndex('cabang_company_status_index');
            $table->dropIndex('cabang_company_default_index');
        });

        Schema::rename('cabang', 'branches');

        Schema::table('branches', function (Blueprint $table) {
            $table->renameColumn('kode', 'code');
            $table->renameColumn('nama', 'name');
            $table->renameColumn('telepon', 'phone');
            $table->renameColumn('alamat', 'address');
            $table->renameColumn('kota', 'city');
            $table->renameColumn('provinsi', 'province');
            $table->renameColumn('kode_pos', 'postal_code');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->cascadeOnDelete();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'created_at']);
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'is_default']);
        });

        Schema::table('outlet', function (Blueprint $table) {
            $table->renameColumn('cabang_id', 'branch_id');
        });

        Schema::table('outlet', function (Blueprint $table) {
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();

            $table->index(['branch_id', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('branch_id')
                ->references('id')
                ->on('branches')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
        });

        Schema::table('outlet', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['branch_id', 'created_at']);
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropUnique(['company_id', 'code']);
            $table->dropIndex(['company_id', 'created_at']);
            $table->dropIndex(['company_id', 'status']);
            $table->dropIndex(['company_id', 'is_default']);
        });

        Schema::table('outlet', function (Blueprint $table) {
            $table->renameColumn('branch_id', 'cabang_id');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->renameColumn('code', 'kode');
            $table->renameColumn('name', 'nama');
            $table->renameColumn('phone', 'telepon');
            $table->renameColumn('address', 'alamat');
            $table->renameColumn('city', 'kota');
            $table->renameColumn('province', 'provinsi');
            $table->renameColumn('postal_code', 'kode_pos');
        });

        Schema::rename('branches', 'cabang');

        Schema::table('cabang', function (Blueprint $table) {
            $table->foreign('company_id')
                ->references('id')
                ->on('companies')
                ->cascadeOnDelete();

            $table->unique(['company_id', 'kode']);
            $table->index(['company_id', 'created_at']);
            $table->index(['company_id', 'status'], 'cabang_company_status_index');
            $table->index(['company_id', 'is_default'], 'cabang_company_default_index');
        });

        Schema::table('outlet', function (Blueprint $table) {
            $table->foreign('cabang_id')
                ->references('id')
                ->on('cabang')
                ->nullOnDelete();

            $table->index(['cabang_id', 'created_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('branch_id')
                ->references('id')
                ->on('cabang')
                ->nullOnDelete();
        });
    }
};