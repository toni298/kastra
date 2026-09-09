<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_timelines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('transfer_transaction_id')->constrained('transfer_transactions')->cascadeOnDelete();
            $table->foreignUuid('user_id')->constrained('users')->restrictOnDelete();
            $table->string('event', 50);
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['transfer_transaction_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_timelines');
    }
};
