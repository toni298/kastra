<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::table('stock_movements', function (Blueprint $table): void {
         $table->string('unit_name')->nullable()->after('product_name');
      });

      DB::table('stock_movements')->orderBy('id')->each(function (object $movement): void {
         $unitName = DB::table('products')
            ->join('units', 'units.id', '=', 'products.unit_id')
            ->where('products.id', $movement->product_id)
            ->value('units.name');

         DB::table('stock_movements')->where('id', $movement->id)->update([
            'unit_name' => $unitName,
         ]);
      });
   }

   public function down(): void
   {
      Schema::table('stock_movements', function (Blueprint $table): void {
         $table->dropColumn('unit_name');
      });
   }
};
