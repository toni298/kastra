<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::table('employees', function (Blueprint $table): void {
         $table->date('contract_ends_at')->nullable()->after('hired_at');
         $table->index(['company_id', 'contract_ends_at', 'status']);
      });
   }

   public function down(): void
   {
      Schema::table('employees', function (Blueprint $table): void {
         $table->dropIndex(['company_id', 'contract_ends_at', 'status']);
         $table->dropColumn('contract_ends_at');
      });
   }
};
