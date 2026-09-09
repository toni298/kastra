<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->index(['company_id', 'payment_status', 'due_date'], 'purchase_due_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_transactions', function (Blueprint $table) {
            $table->dropIndex('purchase_due_date_idx');
        });
    }
};
