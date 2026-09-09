<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('slug', 120)->nullable()->after('name');
        });

        // Backfill unique slugs for existing companies.
        DB::table('companies')->select(['id', 'name'])->orderBy('created_at')->chunkById(100, function ($companies) {
            foreach ($companies as $company) {
                $base = Str::slug($company->name) ?: 'store';
                $slug = $base;
                $counter = 1;
                while (DB::table('companies')->where('slug', $slug)->where('id', '!=', $company->id)->exists()) {
                    $slug = $base.'-'.$counter++;
                }
                DB::table('companies')->where('id', $company->id)->update(['slug' => $slug]);
            }
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->string('slug', 120)->nullable()->after('code');
            $table->string('whatsapp_number', 32)->nullable()->after('phone');
            $table->boolean('is_store_enabled')->default(false)->after('is_default');
        });

        // Backfill per-company unique branch slugs.
        DB::table('branches')->select(['id', 'company_id', 'name'])->orderBy('created_at')->chunkById(100, function ($branches) {
            foreach ($branches as $branch) {
                $base = Str::slug($branch->name) ?: 'cabang';
                $slug = $base;
                $counter = 1;
                while (
                    DB::table('branches')
                        ->where('company_id', $branch->company_id)
                        ->where('slug', $slug)
                        ->where('id', '!=', $branch->id)
                        ->exists()
                ) {
                    $slug = $base.'-'.$counter++;
                }
                DB::table('branches')->where('id', $branch->id)->update(['slug' => $slug]);
            }
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->unique(['company_id', 'slug']);
            $table->index(['company_id', 'is_store_enabled'], 'branches_store_enabled_idx');
        });

        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending', 'confirmed', 'completed', 'return', 'partial_return', 'full_return', 'cancelled'])
                ->default('completed')
                ->change();

            $table->string('order_channel', 20)->default('pos')->after('document_type');
            $table->string('delivery_method', 20)->nullable()->after('order_channel');
            $table->string('shipping_recipient_name')->nullable()->after('delivery_method');
            $table->string('shipping_phone', 32)->nullable()->after('shipping_recipient_name');
            $table->text('shipping_address')->nullable()->after('shipping_phone');
            $table->unsignedBigInteger('shipping_cost')->default(0)->after('shipping_address');
            $table->string('customer_note', 500)->nullable()->after('note');

            $table->index(['company_id', 'order_channel', 'status'], 'sales_store_channel_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropIndex('sales_store_channel_status_idx');
            $table->dropColumn([
                'order_channel',
                'delivery_method',
                'shipping_recipient_name',
                'shipping_phone',
                'shipping_address',
                'shipping_cost',
                'customer_note',
            ]);
            $table->enum('status', ['draft', 'completed', 'return', 'partial_return', 'full_return', 'cancelled'])
                ->default('completed')
                ->change();
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropIndex('branches_store_enabled_idx');
            $table->dropUnique(['company_id', 'slug']);
            $table->dropColumn(['slug', 'whatsapp_number', 'is_store_enabled']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
