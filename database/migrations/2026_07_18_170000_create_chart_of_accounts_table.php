<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('parent_id')->nullable()->constrained('chart_of_accounts')->restrictOnDelete();
            $table->string('kode', 50);
            $table->string('nama');
            $table->enum('kategori', ['aset', 'liabilitas', 'ekuitas', 'pendapatan', 'beban']);
            $table->boolean('is_header')->default(false);
            $table->boolean('is_system')->default(false);
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'kode']);
            $table->index(['company_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
