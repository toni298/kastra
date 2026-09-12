<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
   use UsesUuid;

   protected $fillable = ['company_id', 'name', 'start_time', 'end_time', 'grace_period_minutes', 'is_active'];

   protected function casts(): array
   {
      return ['grace_period_minutes' => 'integer', 'is_active' => 'boolean'];
   }

   public function company(): BelongsTo
   {
      return $this->belongsTo(Company::class);
   }

   public function employees(): HasMany
   {
      return $this->hasMany(Employee::class);
   }
}
