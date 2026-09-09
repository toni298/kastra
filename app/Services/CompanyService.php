<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyService
{
    public function __construct(private RbacService $rbacService) {}

    public function ensureForUser(User $user): Company
    {
        if ($user->company) {
            return $user->company;
        }

        return DB::transaction(function () use ($user) {
            $company = Company::create([
                'name' => $user->name,
                'currency_code' => 'IDR',
                'timezone' => 'Asia/Jakarta',
                'locale' => 'id',
                'country_code' => 'ID',
            ]);

            $user->update(['company_id' => $company->id]);
            $this->rbacService->bootstrapCompany($company);
            $ownerRole = Role::query()
                ->where('company_id', $company->id)
                ->where('name', 'owner')
                ->where('guard_name', 'web')
                ->firstOrFail();
            $user->syncRoles([$ownerRole]);

            return $company;
        });
    }

    public function updateProfile(Company $company, array $data): Company
    {
        return DB::transaction(function () use ($company, $data) {
            $company->update($data);

            return $company->refresh();
        });
    }

    public function updateSettings(Company $company, array $data): Company
    {
        return DB::transaction(function () use ($company, $data) {
            $company->update($data);

            return $company->refresh();
        });
    }

    public function replaceLogo(Company $company, UploadedFile $logo): Company
    {
        return DB::transaction(function () use ($company, $logo) {
            $previousPath = $company->logo_path;
            $filename = Str::uuid().'.'.$logo->extension();
            $path = $logo->storeAs("companies/{$company->id}/logo", $filename, 'public');

            $company->update(['logo_path' => $path]);

            if ($previousPath) {
                Storage::disk('public')->delete($previousPath);
            }

            return $company->refresh();
        });
    }

    public function removeLogo(Company $company): Company
    {
        return DB::transaction(function () use ($company) {
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }

            $company->update(['logo_path' => null]);

            return $company->refresh();
        });
    }
}
