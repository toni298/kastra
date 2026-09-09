<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id')->unique();
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();

            // Tampilan
            $table->string('template')->default('modern');
            $table->string('primary_color', 16)->default('#1d4ed8');
            $table->string('secondary_color', 16)->nullable();
            $table->string('tagline')->nullable();
            $table->string('hero_title')->nullable();
            $table->string('hero_subtitle')->nullable();
            $table->string('banner_path')->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('show_feature_badges')->default(true);

            // Pembayaran
            $table->boolean('payment_cod_enabled')->default(true);
            $table->boolean('payment_transfer_enabled')->default(true);
            $table->boolean('payment_qris_enabled')->default(false);
            $table->string('qris_image_path')->nullable();
            $table->text('payment_notes')->nullable();

            // Pengiriman
            $table->boolean('pickup_enabled')->default(true);
            $table->boolean('delivery_enabled')->default(true);
            $table->unsignedInteger('flat_shipping_cost')->nullable();
            $table->unsignedInteger('free_shipping_min')->nullable();

            // Domain (disiapkan, belum diaktifkan)
            $table->string('subdomain')->nullable()->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('domain_status')->default('inactive');

            // Lainnya
            $table->boolean('is_store_active')->default(true);
            $table->string('whatsapp_number')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
