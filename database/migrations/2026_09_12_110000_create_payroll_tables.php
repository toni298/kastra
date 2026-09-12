<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::create('payrolls', function (Blueprint $table): void {
         $table->uuid('id')->primary(); $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete(); $table->date('period'); $table->date('cutoff_date'); $table->enum('status', ['draft', 'approved', 'posted'])->default('draft'); $table->unsignedBigInteger('total_amount')->default(0); $table->unsignedInteger('employee_count')->default(0); $table->dateTime('posted_at')->nullable(); $table->foreignUuid('posted_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps(); $table->unique(['company_id', 'period']); $table->index(['company_id', 'period', 'status']);
      });
      Schema::create('payroll_items', function (Blueprint $table): void {
         $table->uuid('id')->primary(); $table->foreignUuid('payroll_id')->constrained('payrolls')->cascadeOnDelete(); $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete(); $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete(); $table->unsignedBigInteger('basic_salary')->default(0); $table->unsignedBigInteger('allowance')->default(0); $table->unsignedBigInteger('commission_amount')->default(0); $table->unsignedBigInteger('overtime_amount')->default(0); $table->unsignedBigInteger('deduction')->default(0); $table->unsignedBigInteger('net_salary')->default(0); $table->json('commission_detail')->nullable(); $table->json('overtime_detail')->nullable(); $table->timestamps(); $table->unique(['payroll_id', 'employee_id']);
      });
      Schema::create('payroll_journals', function (Blueprint $table): void {
         $table->uuid('id')->primary(); $table->foreignUuid('payroll_id')->unique()->constrained('payrolls')->cascadeOnDelete(); $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete(); $table->string('journal_number', 50); $table->date('transaction_date'); $table->unsignedBigInteger('amount'); $table->text('description'); $table->foreignUuid('posted_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
      });
   }
   public function down(): void { Schema::dropIfExists('payroll_journals'); Schema::dropIfExists('payroll_items'); Schema::dropIfExists('payrolls'); }
};
