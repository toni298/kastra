<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cash_bank_transactions', function (Blueprint $table): void {
            $table->uuid('cash_bank_account_id')->nullable()->change();
            $table->enum('type', ['in', 'out', 'none'])->change();
            $table->uuid('reference')->nullable()->change();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('cash_bank_account_id')->references('id')->on('cash_bank_accounts')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cash_bank_transactions', function (Blueprint $table): void {
            $table->dropForeign(['company_id', 'cash_bank_account_id', 'created_by']);
            $table->string('reference')->nullable()->change();
            $table->enum('type', ['in', 'out'])->change();
            $table->uuid('cash_bank_account_id')->nullable(false)->change();
        });
    }
};
