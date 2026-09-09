<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tax_configurations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('tax_in_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->foreignUuid('tax_out_account_id')->nullable()->constrained('chart_of_accounts')->nullOnDelete();
            $table->string('kode', 50);
            $table->string('nama');
            $table->enum('jenis', ['penjualan', 'pembelian', 'potong', 'pungut']);
            $table->decimal('persentase', 8, 4);
            $table->enum('mode', ['inclusive', 'exclusive'])->default('exclusive');
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'kode']);
            $table->index(['company_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_configurations');
    }
};
