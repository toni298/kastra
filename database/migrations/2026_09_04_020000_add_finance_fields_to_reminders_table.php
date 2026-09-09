<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::table('reminders', function (Blueprint $table) {
         $table->enum('type_transcation', ['in', 'out'])->default('out')->after('type');
         $table->unsignedBigInteger('amount')->default(0)->after('type_transcation');
         $table->index(['company_id', 'type_transcation']);
      });
   }

   public function down(): void
   {
      Schema::table('reminders', function (Blueprint $table) {
         $table->dropIndex(['company_id', 'type_transcation']);
         $table->dropColumn(['type_transcation', 'amount']);
      });
   }
};
