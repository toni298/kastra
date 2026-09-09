<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('purchase_transaction_id')->constrained('purchase_transactions')->cascadeOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->string('payment_number', 60);
            $table->enum('payment_method', ['transfer_bank', 'tunai', 'giro']);
            $table->unsignedBigInteger('amount');
            $table->date('payment_date');
            $table->string('reference')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'payment_number'], 'pp_company_number_uq');
            $table->index(['company_id', 'purchase_transaction_id', 'payment_date'], 'pp_transaction_date_idx');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('purchase_payments');
    }
};
