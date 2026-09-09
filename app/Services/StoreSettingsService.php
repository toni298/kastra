<?php

namespace App\Services;

use App\Models\Company;
use App\Models\StoreBankAccount;
use App\Models\StoreSetting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StoreSettingsService
{
    /**
     * Ambil (atau buat default) settings untuk company.
     */
    public function getForCompany(Company $company): StoreSetting
    {
        return $company->storeSetting
            ?? $company->storeSetting()->create([]);
    }

    /**
     * Update tampilan (template, warna, hero, banner, logo).
     */
    public function updateAppearance(Company $company, array $data): StoreSetting
    {
        $settings = $this->getForCompany($company);

        $settings->fill([
            'template' => $data['template'] ?? $settings->template,
            'primary_color' => $data['primary_color'] ?? $settings->primary_color,
            'secondary_color' => $data['secondary_color'] ?? $settings->secondary_color,
            'tagline' => $data['tagline'] ?? $settings->tagline,
            'hero_title' => $data['hero_title'] ?? $settings->hero_title,
            'hero_subtitle' => $data['hero_subtitle'] ?? $settings->hero_subtitle,
            'show_feature_badges' => $data['show_feature_badges'] ?? $settings->show_feature_badges,
            'is_store_active' => $data['is_store_active'] ?? $settings->is_store_active,
            'whatsapp_number' => $data['whatsapp_number'] ?? $settings->whatsapp_number,
        ]);

        if (! empty($data['banner']) && $data['banner'] instanceof UploadedFile) {
            $settings->banner_path = $this->replaceFile($settings->banner_path, $data['banner'], 'store/banners');
        }
        if (! empty($data['logo']) && $data['logo'] instanceof UploadedFile) {
            $settings->logo_path = $this->replaceFile($settings->logo_path, $data['logo'], 'store/logos');
        }
        if (! empty($data['remove_banner'])) {
            $this->deleteFile($settings->banner_path);
            $settings->banner_path = null;
        }
        if (! empty($data['remove_logo'])) {
            $this->deleteFile($settings->logo_path);
            $settings->logo_path = null;
        }

        $settings->save();

        return $settings->refresh();
    }

    /**
     * Update pembayaran (toggle COD/transfer/QRIS + QRIS image + rekening).
     */
    public function updatePayment(Company $company, array $data): StoreSetting
    {
        $settings = $this->getForCompany($company);

        DB::transaction(function () use ($company, $settings, $data): void {
            $settings->fill([
                'payment_cod_enabled' => $data['payment_cod_enabled'] ?? $settings->payment_cod_enabled,
                'payment_transfer_enabled' => $data['payment_transfer_enabled'] ?? $settings->payment_transfer_enabled,
                'payment_qris_enabled' => $data['payment_qris_enabled'] ?? $settings->payment_qris_enabled,
                'payment_notes' => $data['payment_notes'] ?? $settings->payment_notes,
            ]);

            if (! empty($data['qris_image']) && $data['qris_image'] instanceof UploadedFile) {
                $settings->qris_image_path = $this->replaceFile($settings->qris_image_path, $data['qris_image'], 'store/qris');
            }
            if (! empty($data['remove_qris_image'])) {
                $this->deleteFile($settings->qris_image_path);
                $settings->qris_image_path = null;
            }

            $settings->save();

            if (array_key_exists('bank_accounts', $data)) {
                $this->syncBankAccounts($company, $data['bank_accounts'] ?? []);
            }
        });

        return $settings->refresh();
    }

    /**
     * Update pengiriman (pickup/delivery toggle + ongkir).
     */
    public function updateShipping(Company $company, array $data): StoreSetting
    {
        $settings = $this->getForCompany($company);

        $settings->update([
            'pickup_enabled' => $data['pickup_enabled'] ?? $settings->pickup_enabled,
            'delivery_enabled' => $data['delivery_enabled'] ?? $settings->delivery_enabled,
            'flat_shipping_cost' => $data['flat_shipping_cost'] ?? null,
            'free_shipping_min' => $data['free_shipping_min'] ?? null,
        ]);

        return $settings->refresh();
    }

    /**
     * Update domain (disimpan, belum diaktifkan).
     */
    public function updateDomain(Company $company, array $data): StoreSetting
    {
        $settings = $this->getForCompany($company);

        $settings->update([
            'subdomain' => $data['subdomain'] ?? $settings->subdomain,
            'custom_domain' => $data['custom_domain'] ?? $settings->custom_domain,
        ]);

        return $settings->refresh();
    }

    /**
     * Update sections (tambah/hapus/duplikat/reorder/toggle/konfigurasi).
     * Skema lego: key unik + type + config; mendukung banyak instance per type.
     */
    public function updateSections(Company $company, array $sections): StoreSetting
    {
        $settings = $this->getForCompany($company);

        $normalized = collect($sections)->values()->map(function ($s, $i) {
            $n = StoreSetting::normalizeSection((array) $s);
            $n['sort'] = $i;
            return $n;
        })->all();

        $meta = $settings->meta ?? [];
        $meta['sections'] = $normalized;
        $settings->meta = $meta;
        $settings->save();

        return $settings->refresh();
    }

    /**
     * Sinkronisasi daftar rekening bank (replace-all).
     */
    public function syncBankAccounts(Company $company, array $accounts): void
    {
        $company->storeBankAccounts()->delete();

        foreach (array_values($accounts) as $i => $account) {
            if (empty($account['bank_name']) || empty($account['account_number'])) {
                continue;
            }
            $company->storeBankAccounts()->create([
                'bank_name' => $account['bank_name'],
                'account_number' => $account['account_number'],
                'account_name' => $account['account_name'] ?? '',
                'is_active' => $account['is_active'] ?? true,
                'sort_order' => $i,
            ]);
        }
    }

    protected function replaceFile(?string $oldPath, UploadedFile $file, string $directory): string
    {
        $this->deleteFile($oldPath);

        return $file->store($directory, 'public');
    }

    protected function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
