<?php

namespace App\Repositories;

use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReminderRepository extends OrganizationRepository
{
    protected string $model = Reminder::class;

    public function paginate(string $companyId, array $filters): LengthAwarePaginator
    {
        $status = $filters['status'] ?? $filters['filter_status'] ?? null;
        $type = $filters['type'] ?? $filters['transaction_type'] ?? null;
        $search = $filters['search'] ?? null;
        $month = $filters['month'] ?? null;
        $tab = $filters['tab'] ?? null;

        return Reminder::query()
            ->where('company_id', $companyId)
            ->with(['branch:id,name'])
            ->when($tab === 'jatuh_tempo', fn($q) => $q
                ->where('status', 'pending')
                ->where('start', '<', now()->startOfDay())
            )
            ->when($tab === 'terjadwal', fn($q) => $q
                ->where('status', 'pending')
                ->where('start', '>=', now()->startOfDay())
            )
            ->when($tab === 'selesai', fn($q) => $q->where('status', 'completed'))
            ->when($status, fn($q, $value) => $q->where('status', $value))
            ->when($type, fn($q, $value) => $q->where('type', $value))
            ->when($search, fn($q, $value) => $q->where(fn($query) => $query
                ->where('title', 'like', "%{$value}%")
                ->orWhere('notes', 'like', "%{$value}%")
            ))
            ->when($month, function ($q, $value) {
                [$year, $monthNumber] = explode('-', $value);
                $start = Carbon::create((int) $year, (int) $monthNumber, 1, 0, 0, 0);
                $end = $start->copy()->endOfMonth();

                $q->whereBetween('start', [$start, $end]);
            })
            ->orderBy('start')
            ->paginate(15);
    }

    public function allForCompany(string $companyId): Collection
    {
        return Reminder::query()
            ->where('company_id', $companyId)
            ->with(['branch:id,name'])
            ->orderBy('start')
            ->get();
    }
}
