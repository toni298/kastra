<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->string('name');
            $table->text('address')->nullable();
            $table->string('telp', 32)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->index(['company_id', 'branch_id', 'status'], 'customer_scope_idx');
        });

        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->foreignUuid('customer_id')->nullable()->after('branch_id')->constrained('customers')->nullOnDelete();
            $table->unsignedBigInteger('discount')->default(0)->after('payment_status');
            $table->unsignedBigInteger('tax')->default(0)->after('discount');
            $table->dropColumn('customer_name');
        });

        Schema::create('sales_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('sales_transaction_id')->constrained('sales_transactions')->cascadeOnDelete();
            $table->string('payment_number', 50);
            $table->enum('payment_method', ['cash', 'transfer', 'qris', 'ewallet']);
            $table->unsignedBigInteger('amount')->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->date('payment_date');
            $table->string('reference_number')->nullable();
            $table->text('note')->nullable();
            $table->foreignUuid('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['company_id', 'payment_number']);
            $table->index(['company_id', 'branch_id', 'payment_date'], 'payment_branch_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_payments');
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'discount', 'tax']);
            $table->string('customer_name')->nullable();
        });
        Schema::dropIfExists('customers');
    }
};
