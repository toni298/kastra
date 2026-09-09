<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_bank_transfers', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->uuid('source_account_id')->index();
            $table->uuid('destination_account_id')->index();
            $table->uuid('created_by')->nullable()->index();
            $table->string('transfer_number')->unique();
            $table->bigInteger('amount');
            $table->date('transfer_date')->index();
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->index(['company_id', 'transfer_date', 'id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_bank_transfers');
    }
};
