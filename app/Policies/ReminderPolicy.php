<?php

namespace App\Policies;

use App\Models\Reminder;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReminderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('reminders.view');
    }

    public function view(User $user, Reminder $reminder): bool
    {
        return $user->can('reminders.view') && $this->belongsToCompany($user, $reminder);
    }

    public function create(User $user): bool
    {
        return $user->can('reminders.create');
    }

    public function update(User $user, Reminder $reminder): bool
    {
        return $user->can('reminders.edit') && $this->belongsToCompany($user, $reminder);
    }

    public function delete(User $user, Reminder $reminder): bool
    {
        return $user->can('reminders.delete') && $this->belongsToCompany($user, $reminder);
    }

    private function belongsToCompany(User $user, Reminder $reminder): bool
    {
        return (string) $user->company_id === (string) $reminder->company_id;
    }
}
