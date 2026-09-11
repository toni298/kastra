<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
   use HasFactory, SoftDeletes, UsesUuid;

   protected $fillable = [
      'company_id',
      'branch_id',
      'nik',
      'name',
      'phone',
      'email',
      'address',
      'role',
      'base_salary',
      'allowance',
      'commission_type',
      'commission_value',
      'status',
      'pin',
      'hired_at',
   ];

   protected $hidden = ['pin'];

   protected function casts(): array
   {
      return [
         'base_salary' => 'integer',
         'allowance' => 'integer',
         'commission_value' => 'decimal:2',
         'hired_at' => 'date',
      ];
   }

   public function company(): BelongsTo
   {
      return $this->belongsTo(Company::class);
   }

   public function branch(): BelongsTo
   {
      return $this->belongsTo(Branch::class);
   }
}
