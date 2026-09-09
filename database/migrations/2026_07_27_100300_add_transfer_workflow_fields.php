<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('transfer_transactions', 'destination_type')) {
            Schema::table('transfer_transactions', fn (Blueprint $table) =>
                $table->enum('destination_type', ['gudang', 'branch'])->default('gudang')->after('source_gudang_id'));
        }
        if (! Schema::hasColumn('transfer_transactions', 'destination_branch_id')) {
            Schema::table('transfer_transactions', fn (Blueprint $table) => $table->foreignUuid('destination_branch_id')->nullable()->after('destination_gudang_id'));
        }
        if (! Schema::hasColumn('transfer_transactions', 'workflow_status')) {
            Schema::table('transfer_transactions', fn (Blueprint $table) => $table->enum('workflow_status', ['in_transit', 'received', 'partially_received', 'cancelled'])->default('in_transit')->after('status'));
        }
        if (! Schema::hasColumn('transfer_transactions', 'received_by')) {
            Schema::table('transfer_transactions', fn (Blueprint $table) => $table->foreignUuid('received_by')->nullable()->after('created_by'));
        }
        if (! Schema::hasColumn('transfer_transactions', 'dispatched_at')) {
            Schema::table('transfer_transactions', fn (Blueprint $table) => $table->timestamp('dispatched_at')->nullable());
        }
        if (! Schema::hasColumn('transfer_transactions', 'received_at')) {
            Schema::table('transfer_transactions', fn (Blueprint $table) => $table->timestamp('received_at')->nullable());
        }

        Schema::table('transfer_transactions', function (Blueprint $table) {
            $table->foreignUuid('destination_gudang_id')->nullable()->change();
        });

        $transactionForeignKeys = collect(Schema::getForeignKeys('transfer_transactions'))->pluck('name');
        if (! $transactionForeignKeys->contains('transfer_transactions_destination_branch_id_foreign')) {
            Schema::table('transfer_transactions', fn (Blueprint $table) => $table->foreign('destination_branch_id')->references('id')->on('branches')->nullOnDelete());
        }
        if (! $transactionForeignKeys->contains('transfer_transactions_received_by_foreign')) {
            Schema::table('transfer_transactions', fn (Blueprint $table) => $table->foreign('received_by')->references('id')->on('users')->nullOnDelete());
        }

        $transactionIndexes = collect(Schema::getIndexes('transfer_transactions'))->pluck('name');
        if (! $transactionIndexes->contains('transfer_transactions_company_id_workflow_status_index')) {
            Schema::table('transfer_transactions', fn (Blueprint $table) => $table->index(['company_id', 'workflow_status']));
        }
        if (! $transactionIndexes->contains('transfer_dest_scope_idx')) {
            Schema::table('transfer_transactions', fn (Blueprint $table) => $table->index(['company_id', 'destination_type', 'destination_branch_id'], 'transfer_dest_scope_idx'));
        }

        if (! Schema::hasColumn('transfer_details', 'received_quantity')) {
            Schema::table('transfer_details', fn (Blueprint $table) => $table->unsignedBigInteger('received_quantity')->default(0)->after('quantity'));
        }
        if (! Schema::hasColumn('transfer_details', 'adjustment_note')) {
            Schema::table('transfer_details', fn (Blueprint $table) => $table->text('adjustment_note')->nullable()->after('received_quantity'));
        }
        if (! Schema::hasColumn('transfer_details', 'received_at')) {
            Schema::table('transfer_details', fn (Blueprint $table) => $table->timestamp('received_at')->nullable()->after('adjustment_note'));
        }
        Schema::table('transfer_details', fn (Blueprint $table) => $table->foreignUuid('destination_stock_id')->nullable()->change());
    }

    public function down(): void
    {
        Schema::table('transfer_details', function (Blueprint $table) {
            $table->dropColumn(['received_quantity', 'adjustment_note', 'received_at']);
            $table->foreignUuid('destination_stock_id')->nullable(false)->change();
        });

        Schema::table('transfer_transactions', function (Blueprint $table) {
            $table->dropForeign(['destination_branch_id', 'received_by']);
            $table->dropColumn(['destination_type', 'destination_branch_id', 'workflow_status', 'received_by', 'dispatched_at', 'received_at']);
            $table->foreignUuid('destination_gudang_id')->nullable(false)->change();
        });
    }
};
