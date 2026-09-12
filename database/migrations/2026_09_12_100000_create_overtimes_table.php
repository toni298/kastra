<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::create('overtimes', function (Blueprint $table): void {
         $table->uuid('id')->primary();
         $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
         $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
         $table->date('overtime_date');
         $table->decimal('hours', 5, 2);
         $table->unsignedBigInteger('hourly_rate')->default(0);
         $table->unsignedBigInteger('total_amount')->default(0);
         $table->text('reason');
         $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
         $table->enum('source', ['manual', 'automatic'])->default('manual');
         $table->foreignUuid('approved_by')->nullable()->constrained('users')->nullOnDelete();
         $table->dateTime('approved_at')->nullable();
         $table->timestamps();
         $table->index(['company_id', 'overtime_date', 'status']);
         $table->index(['company_id', 'employee_id', 'overtime_date']);
      });
   }

   public function down(): void { Schema::dropIfExists('overtimes'); }
};
