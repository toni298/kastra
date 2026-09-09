<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->renameColumnIfNeeded('product_categories', 'nama', 'name');
        $this->renameColumnIfNeeded('product_categories', 'aktif', 'is_active');
        $this->renameColumnIfNeeded('product_brands', 'nama', 'name');
        $this->renameColumnIfNeeded('product_brands', 'aktif', 'is_active');
        $this->renameColumnIfNeeded('units', 'nama', 'name');
        $this->renameColumnIfNeeded('units', 'kode', 'code');
        $this->renameColumnIfNeeded('units', 'aktif', 'is_active');
        $this->renameColumnIfNeeded('products', 'nama', 'name');
        $this->renameColumnIfNeeded('products', 'harga_beli', 'purchase_price');
        $this->renameColumnIfNeeded('products', 'harga_jual', 'selling_price');
        $this->renameColumnIfNeeded('products', 'minimum_stok', 'minimum_stock');
        $this->renameColumnIfNeeded('products', 'deskripsi', 'description');
        $this->renameColumnIfNeeded('products', 'aktif', 'is_active');

        $this->replaceUniqueIndex(
            'product_categories',
            'product_categories_company_id_nama_unique',
            'product_categories_company_id_name_unique',
            ['company_id', 'name'],
        );
        $this->replaceUniqueIndex(
            'product_brands',
            'product_brands_company_id_nama_unique',
            'product_brands_company_id_name_unique',
            ['company_id', 'name'],
        );
        $this->replaceUniqueIndex(
            'units',
            'units_company_id_nama_unique',
            'units_company_id_name_unique',
            ['company_id', 'name'],
        );
        $this->replaceUniqueIndex(
            'units',
            'units_company_id_kode_unique',
            'units_company_id_code_unique',
            ['company_id', 'code'],
        );
    }

    public function down(): void
    {
        $this->replaceUniqueIndex(
            'product_categories',
            'product_categories_company_id_name_unique',
            'product_categories_company_id_nama_unique',
            ['company_id', 'name'],
        );
        $this->replaceUniqueIndex(
            'product_brands',
            'product_brands_company_id_name_unique',
            'product_brands_company_id_nama_unique',
            ['company_id', 'name'],
        );
        $this->replaceUniqueIndex(
            'units',
            'units_company_id_name_unique',
            'units_company_id_nama_unique',
            ['company_id', 'name'],
        );
        $this->replaceUniqueIndex(
            'units',
            'units_company_id_code_unique',
            'units_company_id_kode_unique',
            ['company_id', 'code'],
        );

        $this->renameColumnIfNeeded('products', 'name', 'nama');
        $this->renameColumnIfNeeded('products', 'purchase_price', 'harga_beli');
        $this->renameColumnIfNeeded('products', 'selling_price', 'harga_jual');
        $this->renameColumnIfNeeded('products', 'minimum_stock', 'minimum_stok');
        $this->renameColumnIfNeeded('products', 'description', 'deskripsi');
        $this->renameColumnIfNeeded('products', 'is_active', 'aktif');
        $this->renameColumnIfNeeded('product_categories', 'name', 'nama');
        $this->renameColumnIfNeeded('product_categories', 'is_active', 'aktif');
        $this->renameColumnIfNeeded('product_brands', 'name', 'nama');
        $this->renameColumnIfNeeded('product_brands', 'is_active', 'aktif');
        $this->renameColumnIfNeeded('units', 'name', 'nama');
        $this->renameColumnIfNeeded('units', 'code', 'kode');
        $this->renameColumnIfNeeded('units', 'is_active', 'aktif');
    }

    private function renameColumnIfNeeded(string $tableName, string $from, string $to): void
    {
        if (! Schema::hasColumn($tableName, $from) || Schema::hasColumn($tableName, $to)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($from, $to) {
            $table->renameColumn($from, $to);
        });
    }

    /**
     * @param  list<string>  $columns
     */
    private function replaceUniqueIndex(
        string $tableName,
        string $oldName,
        string $newName,
        array $columns,
    ): void {
        if (! Schema::hasIndex($tableName, $newName)) {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $newName) {
                $table->unique($columns, $newName);
            });
        }

        // MariaDB may use the old composite index to support the company_id
        // foreign key, so its replacement must exist before the old one is dropped.
        if (Schema::hasIndex($tableName, $oldName)) {
            Schema::table($tableName, function (Blueprint $table) use ($oldName) {
                $table->dropUnique($oldName);
            });
        }
    }
};
