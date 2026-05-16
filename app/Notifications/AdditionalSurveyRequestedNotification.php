<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AdditionalSurveyRequestedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Incident $incident,
        public string $message,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Permintaan survey tambahan',
            'body' => $this->message,
            'incident_number' => $this->incident->incident_number,
            'incident_id' => $this->incident->id,
            'url' => url('/survey/upload?incident_id='.$this->incident->id),
        ];
    }
}
