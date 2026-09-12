<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::table('employees', function (Blueprint $table): void {
         $table->foreignUuid('shift_id')->nullable()->after('branch_id')->constrained('shifts')->nullOnDelete();
      });
   }

   public function down(): void
   {
      Schema::table('employees', fn(Blueprint $table) => $table->dropForeign(['shift_id'])->dropColumn('shift_id'));
   }
};
