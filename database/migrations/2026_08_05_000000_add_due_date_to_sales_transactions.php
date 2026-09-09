<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('transaction_date');
            $table->index(['branch_id', 'payment_status', 'due_date'], 'sales_due_date_idx');
        });
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropIndex('sales_due_date_idx');
            $table->dropColumn('due_date');
        });
    }
};
