<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropUnique(['company_id', 'code']);
            $table->dropIndex(['company_id', 'is_active', 'created_at']);
            $table->renameColumn('contact_name', 'contact_supplier');
            $table->dropColumn([
                'code',
                'phone',
                'category',
                'tax_id',
                'payment_term',
                'is_active',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->renameColumn('contact_supplier', 'contact_name');
            $table->string('code', 50)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('category', 100)->nullable();
            $table->string('tax_id', 50)->nullable();
            $table->string('payment_term', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'is_active', 'created_at']);
        });
    }
};
