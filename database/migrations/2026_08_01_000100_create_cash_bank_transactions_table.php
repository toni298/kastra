<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cash_bank_transactions', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->index();
            $table->uuid('cash_bank_account_id')->index();
            $table->uuid('created_by')->nullable()->index();
            $table->string('transaction_number')->unique();
            $table->enum('type', ['in', 'out']);
            $table->string('category');
            $table->bigInteger('amount');
            $table->date('transaction_date')->index();
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->enum('status', ['completed', 'pending'])->default('completed')->index();
            $table->timestamps();
            $table->index(['company_id', 'transaction_date', 'id']);
        });
    }
    public function down(): void { Schema::dropIfExists('cash_bank_transactions'); }
};
