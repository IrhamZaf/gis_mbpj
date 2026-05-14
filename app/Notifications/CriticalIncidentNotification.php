<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CriticalIncidentNotification extends Notification
{
    use Queueable;

    public function __construct(public Incident $incident) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => 'Kawasan kritikal',
            'message' => 'Insiden '.$this->incident->incident_number.' ditandakan sebagai kritikal.',
            'incident_id' => $this->incident->id,
            'risk_level' => $this->incident->risk_level,
            'url' => url('/incidents/'.$this->incident->id),
        ];
    }
}
