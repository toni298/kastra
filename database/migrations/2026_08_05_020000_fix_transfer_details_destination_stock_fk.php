<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transfer_details', function (Blueprint $table) {
            $table->dropForeign(['destination_stock_id']);
            $table->uuid('destination_stock_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('transfer_details', function (Blueprint $table) {
            $table->foreignUuid('destination_stock_id')
                ->nullable()
                ->constrained('product_stocks')
                ->restrictOnDelete()
                ->change();
        });
    }
};
