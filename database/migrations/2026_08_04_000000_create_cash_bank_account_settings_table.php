<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_bank_account_settings', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->uuid('cash_bank_account_id');
            $table->uuid('branch_id')->nullable();
            $table->boolean('can_receive_money')->default(false);
            $table->boolean('can_send_money')->default(false);
            $table->boolean('is_default_receive')->default(false);
            $table->boolean('is_default_payment')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('cash_bank_account_id')->references('id')->on('cash_bank_accounts')->cascadeOnDelete();
            $table->foreign('branch_id')->references('id')->on('branches')->cascadeOnDelete();
            $table->unique(['company_id', 'cash_bank_account_id', 'branch_id'], 'cb_account_setting_scope_unique');
            $table->index(['company_id', 'branch_id', 'is_active'], 'cb_account_setting_scope_active_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_bank_account_settings');
    }
};
