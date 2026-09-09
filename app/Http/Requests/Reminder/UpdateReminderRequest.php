<?php

namespace App\Http\Requests\Reminder;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReminderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'branch_id' => ['nullable', 'uuid', 'exists:branches,id'],
            'title' => ['required', 'string', 'max:255'],
            'start' => ['required', 'date'],
            'end' => ['nullable', 'date'],
            'all_day' => ['nullable', 'boolean'],
            'status' => ['nullable', 'in:pending,completed,cancelled'],
            'type' => ['nullable', 'in:once,daily,weekly,monthly,yearly'],
            'type_transcation' => ['required', 'in:in,out'],
            'amount' => ['required', 'integer', 'min:0'],
            'recurrence_end_mode' => ['nullable', 'in:forever,until,count'],
            'recurrence_until' => ['nullable', 'date', 'after_or_equal:start'],
            'recurrence_count' => ['nullable', 'integer', 'min:1', 'max:999'],
            'scope' => ['nullable', 'in:this,future,all'],
            'occurrence_start' => ['nullable', 'date'],
            'until' => ['nullable', 'date'],
            'color' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Judul event wajib diisi.',
            'start.required' => 'Tanggal mulai wajib diisi.',
            'start.date' => 'Format tanggal mulai tidak valid.',
        ];
    }
}
