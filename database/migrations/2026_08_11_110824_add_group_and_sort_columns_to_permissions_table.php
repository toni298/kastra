<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->string('group')->nullable()->after('guard_name');
            $table->string('subgroup')->nullable()->after('group');
            $table->unsignedSmallInteger('sort_order')->default(0)->after('subgroup');
            $table->index(['guard_name', 'group', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex(['guard_name', 'group', 'sort_order']);
            $table->dropColumn(['group', 'subgroup', 'sort_order']);
        });
    }
};
