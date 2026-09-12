<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::create('attendances', function (Blueprint $table): void {
         $table->uuid('id')->primary();
         $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
         $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
         $table->foreignUuid('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
         $table->date('attendance_date');
         $table->dateTime('clock_in')->nullable();
         $table->dateTime('clock_out')->nullable();
         $table->unsignedInteger('late_minutes')->default(0);
         $table->enum('status', ['present', 'late', 'absent', 'incomplete'])->default('present');
         $table->text('notes')->nullable();
         $table->foreignUuid('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
         $table->timestamps();
         $table->unique(['employee_id', 'attendance_date']);
         $table->index(['company_id', 'attendance_date', 'status']);
      });
   }

   public function down(): void
   {
      Schema::dropIfExists('attendances');
   }
};
