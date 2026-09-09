<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gudang', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('kode', 50)->nullable();
            $table->string('nama');
            $table->string('email')->nullable();
            $table->string('telepon', 32)->nullable();
            $table->text('alamat')->nullable();
            $table->string('kota', 100)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('kode_pos', 16)->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();
            $table->unique(['company_id', 'kode']);
            $table->index(['company_id', 'created_at']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('gudang');
    }
};
