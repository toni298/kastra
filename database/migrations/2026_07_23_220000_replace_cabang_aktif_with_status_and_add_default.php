<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const STATUS_ACTIVE = 'active';

    private const STATUS_INACTIVE = 'inactive';

    public function up(): void
    {
        $cabang = $this->cabangModel();

        Schema::table('cabang', function (Blueprint $table) {
            $table->string('status', 16)->default(self::STATUS_ACTIVE)->after('aktif');
            $table->boolean('is_default')->default(false)->after('status');
            $table->index(['company_id', 'status'], 'cabang_company_status_index');
            $table->index(['company_id', 'is_default'], 'cabang_company_default_index');
        });

        $cabang->newQuery()
            ->where('aktif', false)
            ->update(['status' => self::STATUS_INACTIVE]);

        foreach (
            $cabang->newQuery()
                ->select('company_id')
                ->distinct()
                ->orderBy('company_id')
                ->cursor() as $branchCompany
        ) {
            $defaultCabangId = $cabang->newQuery()
                ->where('company_id', $branchCompany->company_id)
                ->oldest('created_at')
                ->orderBy('id')
                ->value('id');

            if ($defaultCabangId !== null) {
                $cabang->newQuery()
                    ->whereKey($defaultCabangId)
                    ->update(['is_default' => true]);
            }
        }

        Schema::table('cabang', function (Blueprint $table) {
            $table->dropColumn('aktif');
        });
    }

    public function down(): void
    {
        $cabang = $this->cabangModel();

        Schema::table('cabang', function (Blueprint $table) {
            $table->boolean('aktif')->default(true)->after('kode_pos');
        });

        $cabang->newQuery()
            ->where('status', self::STATUS_INACTIVE)
            ->update(['aktif' => false]);

        Schema::table('cabang', function (Blueprint $table) {
            $table->dropIndex('cabang_company_status_index');
            $table->dropIndex('cabang_company_default_index');
            $table->dropColumn(['status', 'is_default']);
        });
    }

    private function cabangModel(): Model
    {
        return new class extends Model {
            protected $table = 'cabang';

            protected $fillable = [
                'aktif',
                'status',
                'is_default',
            ];
        };
    }
};
