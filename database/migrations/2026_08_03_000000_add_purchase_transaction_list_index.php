<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_transactions', function (Blueprint $table): void {
            // Default list: tenant scope followed by stable newest-first cursor pagination.
            $table->index(['company_id', 'transaction_date', 'id'], 'purchase_company_date_id_idx');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_transactions', function (Blueprint $table): void {
            $table->dropIndex('purchase_company_date_id_idx');
        });
    }
};
