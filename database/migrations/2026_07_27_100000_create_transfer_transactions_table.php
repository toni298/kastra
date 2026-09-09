<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('source_gudang_id')->constrained('gudang')->restrictOnDelete();
            $table->foreignUuid('destination_gudang_id')->constrained('gudang')->restrictOnDelete();
            $table->string('transfer_number', 50);
            $table->date('transfer_date');
            $table->enum('status', ['completed', 'cancelled'])->default('completed');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'transfer_number']);
            $table->index(['company_id', 'transfer_date']);
            $table->index(['company_id', 'source_gudang_id']);
            $table->index(['company_id', 'destination_gudang_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_transactions');
    }
};
