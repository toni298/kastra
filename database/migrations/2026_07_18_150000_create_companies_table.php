<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('legal_name')->nullable();
            $table->string('npwp', 32)->nullable()->unique();
            $table->string('nib', 32)->nullable()->unique();
            $table->string('email')->nullable();
            $table->string('phone', 32)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 16)->nullable();
            $table->char('country_code', 2)->default('ID');
            $table->char('currency_code', 3)->default('IDR');
            $table->string('timezone', 64)->default('Asia/Jakarta');
            $table->string('locale', 10)->default('id');
            $table->string('logo_path')->nullable();
            $table->timestamps();

            $table->index(['name', 'created_at']);
            $table->index(['currency_code', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
