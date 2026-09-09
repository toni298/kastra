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
         $table->string('product_sku')->nullable()->after('product_id');
         $table->string('product_name')->nullable()->after('product_sku');
         $table->string('location_name')->nullable()->after('gudang_id');
         $table->string('reference_number')->nullable()->after('stock_after');
         $table->string('user_name')->nullable()->after('user_id');
      });

      DB::table('stock_movements')->orderBy('id')->each(function (object $movement): void {
         $product = DB::table('products')->where('id', $movement->product_id)->first(['sku', 'name']);
         $location = $movement->gudang_id
            ? DB::table('gudang')->where('id', $movement->gudang_id)->value('nama')
            : DB::table('branches')->where('id', $movement->branch_id)->value('name');
         $userName = $movement->user_id ? DB::table('users')->where('id', $movement->user_id)->value('name') : null;

         DB::table('stock_movements')->where('id', $movement->id)->update([
            'product_sku' => $product?->sku,
            'product_name' => $product?->name,
            'location_name' => $location ?? 'Lokasi tidak diketahui',
            'type' => $movement->type ?? ($movement->movement_type === 'in' ? 'IN' : 'OUT'),
            'qty' => $movement->qty ?? $movement->quantity,
            'reference_number' => 'LEGACY-' . $movement->id,
            'user_name' => $userName ?? 'System',
            'notes' => $movement->notes ?? $movement->note,
         ]);
      });

      Schema::table('stock_movements', function (Blueprint $table): void {
         $table->string('movement_type')->change();
         $table->dropIndex('stock_movements_reference_index');
         $table->dropColumn(['reference_type', 'reference_id']);
         $table->dropColumn(['quantity', 'note']);
         $table->index(['product_id', 'created_at'], 'stock_movements_product_created_index');
         $table->index(['movement_type', 'created_at'], 'stock_movements_type_created_index');
         $table->index('reference_number', 'stock_movements_reference_number_index');
      });
   }

   public function down(): void
   {
      Schema::table('stock_movements', function (Blueprint $table): void {
         $table->uuid('reference_id')->nullable()->after('stock_after');
         $table->string('reference_type')->nullable()->after('reference_id');
         $table->bigInteger('quantity')->nullable()->after('movement_type');
         $table->text('note')->nullable()->after('user_name');
         $table->dropIndex('stock_movements_product_created_index');
         $table->dropIndex('stock_movements_type_created_index');
         $table->dropIndex('stock_movements_reference_number_index');
         $table->dropColumn(['product_sku', 'product_name', 'location_name', 'reference_number', 'user_name']);
      });

      Schema::table('stock_movements', function (Blueprint $table): void {
         $table->enum('movement_type', ['in', 'out', 'adjustment'])->change();
         $table->index(['reference_type', 'reference_id'], 'stock_movements_reference_index');
      });
   }
};
