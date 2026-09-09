<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('number_generators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('document_type', 80);
            $table->string('prefix', 30);
            $table->string('format', 150);
            $table->enum('reset_period', ['never', 'yearly', 'monthly'])->default('never');
            $table->unsignedBigInteger('current_sequence')->default(0);
            $table->date('sequence_period')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('number_generators');
    }
};
