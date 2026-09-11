<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::create('employees', function (Blueprint $table): void {
         $table->uuid('id')->primary();
         $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
         $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
         $table->string('nik', 50);
         $table->string('name');
         $table->string('phone', 32)->nullable();
         $table->string('email')->nullable();
         $table->text('address')->nullable();
         $table->enum('role', ['cashier', 'supervisor', 'staff'])->default('staff');
         $table->unsignedBigInteger('base_salary')->default(0);
         $table->unsignedBigInteger('allowance')->default(0);
         $table->enum('commission_type', ['percentage', 'per_quantity'])->default('percentage');
         $table->decimal('commission_value', 12, 2)->default(0);
         $table->enum('status', ['active', 'inactive'])->default('active');
         $table->string('pin')->nullable();
         $table->date('hired_at')->nullable();
         $table->timestamps();
         $table->softDeletes();

         $table->unique(['company_id', 'nik']);
         $table->index(['company_id', 'status', 'created_at']);
         $table->index(['company_id', 'role', 'created_at']);
      });
   }

   public function down(): void
   {
      Schema::dropIfExists('employees');
   }
};
