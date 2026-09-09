<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::dropIfExists('reminders');

      Schema::create('reminders', function (Blueprint $table) {
         $table->uuid('id')->primary();
         $table->foreignUuid('company_id')->nullable()->constrained('companies')->nullOnDelete();
         $table->foreignUuid('branch_id')->nullable()->constrained('branches')->nullOnDelete();
         $table->string('title');
         $table->dateTime('start');
         $table->dateTime('end')->nullable();
         $table->boolean('all_day')->default(false);
         $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
         $table->enum('type', ['once', 'daily', 'weekly', 'monthly', 'yearly'])->default('once');
         $table->string('color')->nullable();
         $table->text('notes')->nullable();
         $table->text('rrule')->nullable();
         $table->json('exdates')->nullable();
         $table->timestamps();
         $table->softDeletes();

         $table->index(['company_id', 'start']);
         $table->index(['company_id', 'status']);
         $table->index(['branch_id', 'start']);
      });
   }

   public function down(): void
   {
      Schema::dropIfExists('reminders');
   }
};
