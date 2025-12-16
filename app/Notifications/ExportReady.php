<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExportReady extends Notification
{
    use Queueable;

    protected $filename;

    /**
     * Create a new notification instance.
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Kita simpan ke database. Tambahkan 'mail' jika ingin kirim email juga.
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     * Data ini yang akan masuk ke kolom `data` di tabel `notifications`.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Export Selesai',
            'message' => 'File rekaman Anda siap diunduh.',
            'filename' => $this->filename,
            // Kita buat URL download di sini agar di frontend tinggal klik
            'download_url' => route('playback.download', ['filename' => $this->filename]),
            'icon' => 'fas fa-file-archive', // Opsional: icon untuk UI
            'color' => 'text-emerald-500',   // Opsional: warna untuk UI
        ];
    }
}