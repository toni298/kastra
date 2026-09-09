<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void { Schema::table('purchase_transactions', fn (Blueprint $table) => $table->enum('status', ['draft','ordered','received','completed','closed','cancelled'])->default('draft')->change()); }
    public function down(): void { Schema::table('purchase_transactions', fn (Blueprint $table) => $table->enum('status', ['draft','ordered','received','completed','cancelled'])->default('draft')->change()); }
};
