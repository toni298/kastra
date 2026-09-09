<?php

namespace App\Services;

use App\Models\Reminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReminderService
{
    public function create(string $companyId, array $data): Reminder
    {
        return DB::transaction(function () use ($companyId, $data) {
            return Reminder::create([
                'company_id' => $companyId,
                'branch_id' => $data['branch_id'] ?? null,
                'title' => $data['title'] ?? $data['name'] ?? 'Event',
                'start' => $this->normalizeDateTime($data['start'] ?? $data['due_date'] ?? now()->toDateTimeString()),
                'end' => isset($data['end']) && $data['end'] ? $this->normalizeDateTime($data['end']) : null,
                'all_day' => $data['all_day'] ?? false,
                'status' => $data['status'] ?? 'pending',
                'type' => $data['type'] ?? 'once',
                'type_transcation' => $data['type_transcation'] ?? 'out',
                'amount' => $data['amount'] ?? 0,
                'color' => $data['color'] ?? null,
                'notes' => $data['notes'] ?? null,
                'rrule' => $this->buildRRule($data),
                'exdates' => [],
            ]);
        });
    }

    public function update(Reminder $reminder, array $data): void
    {
        DB::transaction(function () use ($reminder, $data) {
            if (($data['scope'] ?? 'all') === 'future' && $reminder->rrule && ! empty($data['occurrence_start'])) {
                $this->splitRecurrence($reminder, $data);

                return;
            }

            if (($data['scope'] ?? 'all') === 'this' && $reminder->rrule && ! empty($data['occurrence_start'])) {
                $this->addException($reminder, $data['occurrence_start']);
                $this->create($reminder->company_id, [
                    ...$data,
                    'type' => 'once',
                    'rrule' => null,
                    'start' => $data['start'],
                    'end' => $data['end'] ?? null,
                ]);

                return;
            }

            $reminder->update([
                'branch_id' => $data['branch_id'] ?? $reminder->branch_id,
                'title' => $data['title'] ?? $data['name'] ?? $reminder->title,
                'start' => $this->normalizeDateTime($data['start'] ?? $data['due_date'] ?? $reminder->start->toDateTimeString()),
                'end' => isset($data['end']) && $data['end'] ? $this->normalizeDateTime($data['end']) : ($reminder->end ? $reminder->end->toDateTimeString() : null),
                'all_day' => $data['all_day'] ?? $reminder->all_day,
                'status' => $data['status'] ?? $reminder->status,
                'type' => $data['type'] ?? $reminder->type,
                'type_transcation' => $data['type_transcation'] ?? $reminder->type_transcation,
                'amount' => $data['amount'] ?? $reminder->amount,
                'color' => $data['color'] ?? $reminder->color,
                'notes' => $data['notes'] ?? $reminder->notes,
                'rrule' => array_key_exists('recurrence_end_mode', $data)
                    ? $this->buildRRule($data)
                    : $reminder->rrule,
            ]);
        });
    }

    public function delete(Reminder $reminder): void
    {
        DB::transaction(fn() => $reminder->delete());
    }

    public function deleteOccurrence(Reminder $reminder, string $occurrenceStart): void
    {
        DB::transaction(fn() => $this->addException($reminder, $occurrenceStart));
    }

    public function deleteFuture(Reminder $reminder, string $occurrenceStart): void
    {
        DB::transaction(function () use ($reminder, $occurrenceStart) {
            $cutOffDate = Carbon::parse($occurrenceStart)->startOfDay()->subDay()->endOfDay();
            $reminder->update([
                'rrule' => $this->replaceUntil($reminder->rrule, $cutOffDate),
            ]);
        });
    }

    public function complete(Reminder $reminder): void
    {
        DB::transaction(fn() => $reminder->update(['status' => 'completed']));
    }

    public function cancel(Reminder $reminder): void
    {
        DB::transaction(fn() => $reminder->update(['status' => 'cancelled']));
    }

    private function normalizeDateTime(string $value): string
    {
        return date('Y-m-d H:i:s', strtotime($value));
    }

    private function buildRRule(array $data): ?string
    {
        $type = $data['type'] ?? 'once';
        if ($type === 'once') {
            return null;
        }

        $rule = 'FREQ=' . strtoupper($type);
        $mode = $data['recurrence_end_mode'] ?? 'forever';

        if ($mode === 'until' && ! empty($data['recurrence_until'])) {
            $rule .= ';UNTIL=' . Carbon::parse($data['recurrence_until'])->format('Ymd\\THis\\Z');
        }

        if ($mode === 'count' && ! empty($data['recurrence_count'])) {
            $rule .= ';COUNT=' . (int) $data['recurrence_count'];
        }

        return $rule;
    }

    private function addException(Reminder $reminder, string $occurrenceStart): void
    {
        $exceptions = $reminder->exdates ?? [];
        $exception = Carbon::parse($occurrenceStart)->toISOString();

        if (! in_array($exception, $exceptions, true)) {
            $reminder->update(['exdates' => [...$exceptions, $exception]]);
        }
    }

    private function splitRecurrence(Reminder $reminder, array $data): void
    {
        $selectedDate = Carbon::parse($data['occurrence_start']);
        $cutOffDate = $selectedDate->copy()->startOfDay()->subDay()->endOfDay();
        $createData = $data['create'] ?? $data;

        $reminder->update([
            'rrule' => $this->replaceUntil($reminder->rrule, $cutOffDate),
        ]);

        $this->create($reminder->company_id, [
            ...$createData,
            'branch_id' => $createData['branch_id'] ?? $reminder->branch_id,
            'type' => $createData['type'] ?? $reminder->type,
            'start' => $createData['start'],
            'end' => $createData['end'] ?? null,
            'recurrence_end_mode' => $createData['recurrence_end_mode'] ?? 'forever',
        ]);
    }

    private function replaceUntil(string $rrule, Carbon $until): string
    {
        $parts = collect(explode(';', $rrule))
            ->reject(fn(string $part) => str_starts_with(strtoupper($part), 'UNTIL=') || str_starts_with(strtoupper($part), 'COUNT='))
            ->values()
            ->all();

        $parts[] = 'UNTIL=' . $until->utc()->format('Ymd\\THis\\Z');

        return implode(';', $parts);
    }
}
