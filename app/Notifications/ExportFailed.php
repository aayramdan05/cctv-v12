<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class ExportFailed extends Notification
{
    use Queueable;

    protected $reason;

    public function __construct($reason)
    {
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Export Gagal',
            'message' => $this->reason,
            'icon' => 'fas fa-exclamation-triangle',
            'color' => 'text-red-500',
        ];
    }
}
