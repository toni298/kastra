<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reminder extends Model
{
    use SoftDeletes, UsesUuid;

    protected $appends = ['name', 'due_date'];

    protected $fillable = [
        'company_id',
        'branch_id',
        'title',
        'start',
        'end',
        'all_day',
        'status',
        'type',
        'type_transcation',
        'amount',
        'color',
        'notes',
        'rrule',
        'exdates',
    ];

    protected $casts = [
        'start' => 'datetime',
        'end' => 'datetime',
        'all_day' => 'boolean',
        'status' => 'string',
        'type' => 'string',
        'type_transcation' => 'string',
        'amount' => 'integer',
        'exdates' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getNameAttribute(): string
    {
        return $this->title ?? '';
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['title'] = $value;
    }

    public function getDueDateAttribute(): ?string
    {
        return $this->start ? $this->start->format('Y-m-d') : null;
    }

    public function setDueDateAttribute($value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['start'] = null;

            return;
        }

        $this->attributes['start'] = \Illuminate\Support\Carbon::parse($value)->startOfDay()->toDateTimeString();
    }

    public function scopeStatusFilter($query, ?string $status)
    {
        if (! $status || $status === 'all') {
            return $query;
        }

        return $query->where('status', $status);
    }

    public function scopeTypeFilter($query, ?string $type)
    {
        if (! $type || $type === 'all') {
            return $query;
        }

        return $query->where('type', $type);
    }

    public function scopeSearch($query, ?string $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where('title', 'like', "%{$search}%")
            ->orWhere('notes', 'like', "%{$search}%");
    }
}
