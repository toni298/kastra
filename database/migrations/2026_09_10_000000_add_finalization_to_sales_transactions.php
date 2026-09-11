<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table): void {
            $table->timestamp('finalized_at')->nullable()->after('status');
            $table->foreignUuid('finalized_by')
                ->nullable()
                ->after('finalized_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['company_id', 'finalized_at']);
        });
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table): void {
            $table->dropForeign(['finalized_by']);
            $table->dropIndex(['company_id', 'finalized_at']);
            $table->dropColumn(['finalized_at', 'finalized_by']);
        });
    }
};
