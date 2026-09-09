<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory, UsesUuid;

    protected $fillable = [
        'name',
        'slug',
        'legal_name',
        'npwp',
        'nib',
        'email',
        'phone',
        'address',
        'city',
        'province',
        'postal_code',
        'country_code',
        'currency_code',
        'timezone',
        'locale',
        'organization_mode',
        'features',
        'logo_path',
    ];

    protected $casts = [
        'features' => 'array',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function cabang(): HasMany
    {
        return $this->branches();
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function gudang(): HasMany
    {
        return $this->hasMany(Gudang::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class);
    }

    public function productBrands(): HasMany
    {
        return $this->hasMany(ProductBrand::class);
    }

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function productImages(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function initialProductStocks(): HasMany
    {
        return $this->hasMany(InitialProductStock::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    public function cashBankAccountSettings(): HasMany
    {
        return $this->hasMany(CashBankAccountSetting::class);
    }

    public function storeSetting(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(StoreSetting::class);
    }

    public function storeBankAccounts(): HasMany
    {
        return $this->hasMany(StoreBankAccount::class)->orderBy('sort_order');
    }
}
