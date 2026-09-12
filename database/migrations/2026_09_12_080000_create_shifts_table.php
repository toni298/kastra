<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::create('shifts', function (Blueprint $table): void {
         $table->uuid('id')->primary();
         $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
         $table->string('name');
         $table->time('start_time');
         $table->time('end_time');
         $table->unsignedSmallInteger('grace_period_minutes')->default(0);
         $table->boolean('is_active')->default(true);
         $table->timestamps();
         $table->unique(['company_id', 'name']);
      });
   }

   public function down(): void
   {
      Schema::dropIfExists('shifts');
   }
};
