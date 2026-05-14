<?php

namespace App\Observers;

use App\Models\Incident;
use App\Models\User;
use App\Notifications\CriticalIncidentNotification;

class IncidentObserver
{
    public function created(Incident $incident): void
    {
        $this->notifyIfCritical($incident);
    }

    public function updated(Incident $incident): void
    {
        if ($incident->wasChanged('risk_level')) {
            $this->notifyIfCritical($incident);
        }
    }

    protected function notifyIfCritical(Incident $incident): void
    {
        if ($incident->risk_level !== Incident::RISK_CRITICAL) {
            return;
        }
        $users = User::query()
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_ENGINEER])
            ->get();
        foreach ($users as $user) {
            $user->notify(new CriticalIncidentNotification($incident));
        }
    }
}
