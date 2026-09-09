<?php

namespace App\Actions;

use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReminderCalendarService
{
    public function buildEvents(string $companyId, ?string $selectedMonth = null): Collection
    {
        $query = Reminder::query()
            ->where('company_id', $companyId)
            ->with(['branch:id,name'])
            ->orderBy('start');

        if ($selectedMonth) {
            [$year, $month] = explode('-', $selectedMonth);
            $monthStart = Carbon::create((int) $year, (int) $month, 1)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $query->where('start', '<=', $monthEnd)
                ->where(function ($query) use ($monthStart) {
                    $query->where('start', '>=', $monthStart)
                        ->orWhereNotNull('rrule');
                });
        }

        return $query->get()->map(function (Reminder $reminder) {
            return [
                'id' => $reminder->id,
                'title' => $reminder->title,
                'start' => $reminder->rrule ? null : $reminder->start?->toISOString(),
                'end' => $reminder->rrule ? null : $reminder->end?->toISOString(),
                'allDay' => (bool) $reminder->all_day,
                'color' => $reminder->color ?? $this->statusColor($reminder->status),
                'rrule' => $this->parseRRule($reminder),
                'exdate' => $reminder->exdates ?? [],
                'extendedProps' => [
                    'id' => $reminder->id,
                    'title' => $reminder->title,
                    'status' => $reminder->status,
                    'type' => $reminder->type,
                    'type_transcation' => $reminder->type_transcation,
                    'amount' => $reminder->amount,
                    'color' => $reminder->color ?? $this->statusColor($reminder->status),
                    'all_day' => (bool) $reminder->all_day,
                    'notes' => $reminder->notes,
                    'branch' => $reminder->branch,
                    'start' => $reminder->start?->toDateTimeString(),
                    'end' => $reminder->end?->toDateTimeString(),
                    'rrule' => $reminder->rrule,
                    'exdates' => $reminder->exdates ?? [],
                ],
            ];
        })->values();
    }

    public function buildUpcoming(string $companyId, int $days = 7, ?string $dateFrom = null, ?string $dateTo = null): Collection
    {
        $rangeStart = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : now()->startOfDay();
        $rangeEnd = $dateTo ? Carbon::parse($dateTo)->endOfDay() : now()->addDays($days)->endOfDay();

        return Reminder::query()
            ->where('company_id', $companyId)
            ->where('status', 'pending')
            ->whereBetween('start', [$rangeStart, $rangeEnd])
            ->orderBy('start')
            ->limit(5)
            ->get()
            ->map(fn(Reminder $reminder) => [
                'id' => $reminder->id,
                'title' => $reminder->title,
                'start' => $reminder->start,
                'status' => $reminder->status,
                'type' => $reminder->type,
                'type_transcation' => $reminder->type_transcation,
                'amount' => $reminder->amount,
                'color' => $reminder->color ?? $this->statusColor($reminder->status),
            ]);
    }

    public function pendingOutAmountForMonth(string $companyId, ?string $dateFrom = null, ?string $dateTo = null): int
    {
        $rangeStart = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : now()->startOfMonth();
        $rangeEnd = $dateTo ? Carbon::parse($dateTo)->endOfDay() : now()->endOfMonth();

        return (int) Reminder::query()
            ->where('company_id', $companyId)
            ->where('status', 'pending')
            ->where('type_transcation', 'out')
            ->whereBetween('start', [$rangeStart, $rangeEnd])
            ->sum('amount');
    }

    public function buildStats(string $companyId): array
    {
        return [
            'total' => Reminder::query()->where('company_id', $companyId)->count(),
            'due_today' => Reminder::query()->where('company_id', $companyId)->whereDate('start', today())->count(),
            'completed_this_month' => Reminder::query()->where('company_id', $companyId)->where('status', 'completed')->whereMonth('start', now()->month)->count(),
            'upcoming_amount' => 0,
        ];
    }

    private function statusColor(string $status): string
    {
        return match ($status) {
            'completed' => '#10b981',
            'cancelled' => '#64748b',
            default => '#3b82f6',
        };
    }

    private function parseRRule(Reminder $reminder): ?array
    {
        if (! $reminder->rrule) {
            return null;
        }

        $parts = collect(explode(';', $reminder->rrule))
            ->mapWithKeys(function (string $part) {
                [$key, $value] = array_pad(explode('=', $part, 2), 2, null);

                return [strtolower($key) => $value];
            });

        $rule = [
            'freq' => strtolower((string) $parts->get('freq', 'daily')),
            'dtstart' => $reminder->start?->toISOString(),
        ];

        if ($parts->get('until')) {
            $rule['until'] = Carbon::createFromFormat('Ymd\\THis\\Z', $parts->get('until'))->toISOString();
        }

        if ($parts->get('count')) {
            $rule['count'] = (int) $parts->get('count');
        }

        return $rule;
    }
}
