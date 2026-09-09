<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
   {
      Schema::table('stock_movements', function (Blueprint $table): void {
         $table->enum('movement_type', [
            'in',
            'out',
            'adjustment',
            'SALE',
            'PURCHASE',
            'OPNAME_ADJUSTMENT',
            'VOID_SALE',
            'RETUR_CUSTOMER',
            'RETUR_SUPPLIER',
            'DAMAGE',
            'TRANSFER_IN',
            'TRANSFER_OUT',
         ])->change();
         $table->enum('type', ['IN', 'OUT'])->nullable()->after('product_id');
         $table->foreignUuid('gudang_id')->nullable()->after('branch_id')->constrained('gudang')->nullOnDelete();
         $table->decimal('qty', 18, 3)->nullable()->after('quantity');
         $table->decimal('stock_before', 18, 3)->nullable()->after('qty');
         $table->decimal('stock_after', 18, 3)->nullable()->after('stock_before');
         $table->foreignUuid('user_id')->nullable()->after('reference_id')->constrained('users')->nullOnDelete();
         $table->text('notes')->nullable()->after('note');
      });

      Schema::table('stock_movements', function (Blueprint $table): void {
         $table->index('product_id', 'stock_movements_product_id_index');
         $table->index('movement_type', 'stock_movements_movement_type_index');
         $table->index('created_at', 'stock_movements_created_at_index');
         $table->index(['reference_type', 'reference_id'], 'stock_movements_reference_index');
         $table->index(['company_id', 'product_id', 'gudang_id', 'created_at'], 'stock_movements_ledger_scope_index');
      });
   }

   public function down(): void
   {
      Schema::table('stock_movements', function (Blueprint $table): void {
         $table->dropIndex('stock_movements_ledger_scope_index');
         $table->dropIndex('stock_movements_reference_index');
         $table->dropIndex('stock_movements_created_at_index');
         $table->dropIndex('stock_movements_movement_type_index');
         $table->dropIndex('stock_movements_product_id_index');
         $table->dropForeign(['gudang_id']);
         $table->dropForeign(['user_id']);
         $table->dropColumn(['type', 'gudang_id', 'qty', 'stock_before', 'stock_after', 'user_id', 'notes']);
      });
   }
};
