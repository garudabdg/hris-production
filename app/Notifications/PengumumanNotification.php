<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Crypt;

class PengumumanNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $pengumuman;

    /**
     * Create a new notification instance.
     */
    public function __construct($pengumuman)
    {
        $this->pengumuman = $pengumuman;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Send via database and email if user has email
        $channels = ['database'];
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = route('pengumuman.show', Crypt::encrypt($this->pengumuman->id));
        
        return (new MailMessage)
            ->subject('📢 Pengumuman Baru: ' . $this->pengumuman->judul)
            ->view('emails.pengumuman', [
                'notifiable' => $notifiable,
                'pengumuman' => $this->pengumuman,
                'url' => $url
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Pengumuman Baru: ' . $this->pengumuman->judul,
            'message' => substr(strip_tags($this->pengumuman->isi), 0, 100) . '...',
            'url' => route('pengumuman.show', Crypt::encrypt($this->pengumuman->id)),
            'type' => 'pengumuman',
            'icon' => 'ti-bell'
        ];
    }
}
