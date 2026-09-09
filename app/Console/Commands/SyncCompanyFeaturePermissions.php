<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\RbacService;
use Illuminate\Console\Command;

class SyncCompanyFeaturePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:sync-feature-permissions
                            {--company= : Sync hanya untuk company ID tertentu}
                            {--all : Sync semua company (termasuk yang features-nya null)}
                            {--force : Konfirmasi otomatis tanpa prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Re-sync permission roles (owner, admin, karyawan) berdasarkan fitur yang dipilih saat onboarding';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $rbac = app(RbacService::class);
        $companyId = $this->option('company');
        $syncAll = $this->option('all');

        // Build query
        $query = Company::query()->orderBy('created_at');

        if ($companyId) {
            $query->where('id', $companyId);
        } elseif (! $syncAll) {
            // Default: hanya company yang sudah punya features (onboarding baru)
            $query->whereNotNull('features');
        }

        $companies = $query->get();

        if ($companies->isEmpty()) {
            $this->warn('Tidak ada company yang ditemukan.');
            if (! $syncAll && ! $companyId) {
                $this->info('Gunakan --all untuk sync semua company, atau --company=<id> untuk company tertentu.');
            }
            return 1;
        }

        $this->info("Ditemukan {$companies->count()} company untuk di-sync.");
        $this->newLine();

        // Preview table
        $rows = [];
        foreach ($companies as $company) {
            $features = $company->features ?? [];
            $featureLabel = empty($features) ? '(semua / fallback)' : implode(', ', $features);
            $permCount = count($rbac->permissionsForFeatures($features));
            $rows[] = [$company->name, $featureLabel, $permCount];
        }
        $this->table(['Company', 'Features', 'Permissions'], $rows);

        // Confirm
        if (! $this->option('force')) {
            if (! $this->confirm('Lanjutkan re-sync permission untuk company di atas?', true)) {
                $this->info('Dibatalkan.');
                return 0;
            }
        }

        // Sync
        $bar = $this->output->createProgressBar($companies->count());
        $bar->start();

        foreach ($companies as $company) {
            $features = $company->features ?? null;
            $rbac->bootstrapCompany($company, $features);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('✅ Sync selesai! Permission roles sudah disesuaikan dengan fitur masing-masing company.');

        return 0;
    }
}
