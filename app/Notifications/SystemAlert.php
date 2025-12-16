<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class SystemAlert extends Notification
{
    use Queueable;

    protected $title;
    protected $message;
    protected $level; // 'info', 'warning', 'danger', 'success'

    /**
     * Create a new notification instance.
     * @param string $title Judul Alert
     * @param string $message Pesan Detail
     * @param string $level Tingkat urgensi (info/warning/danger/success)
     */
    public function __construct($title, $message, $level = 'info')
    {
        $this->title = $title;
        $this->message = $message;
        $this->level = $level;
    }

    public function via(object $notifiable): array
    {
        return ['database']; // Simpan ke database
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'level' => $this->level,
            // Simpan icon & warna agar mudah dirender di frontend
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'bg_color' => $this->getBgColor(),
        ];
    }

    private function getIcon()
    {
        return match($this->level) {
            'danger' => 'fas fa-exclamation-circle',
            'warning' => 'fas fa-exclamation-triangle',
            'success' => 'fas fa-check-circle',
            default => 'fas fa-info-circle',
        };
    }

    private function getColor()
    {
        return match($this->level) {
            'danger' => 'text-red-500',
            'warning' => 'text-amber-500',
            'success' => 'text-emerald-500',
            default => 'text-blue-500',
        };
    }

    private function getBgColor()
    {
        return match($this->level) {
            'danger' => 'bg-red-50',
            'warning' => 'bg-amber-50',
            'success' => 'bg-emerald-50',
            default => 'bg-blue-50',
        };
    }
}