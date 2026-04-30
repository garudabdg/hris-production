<?php

namespace App\Notifications;

use App\Models\Recruitment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewRecruitmentNotification extends Notification
{
    use Queueable;

    public function __construct(protected Recruitment $recruitment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'                => 'new_recruitment',
            'title'               => '👤 Pelamar Baru',
            'message'             => "{$this->recruitment->nama_lengkap} melamar posisi {$this->recruitment->posisi_dilamar}.",
            'kode_recruitment'    => $this->recruitment->kode_recruitment,
            'nama_lengkap'        => $this->recruitment->nama_lengkap,
            'posisi_dilamar'      => $this->recruitment->posisi_dilamar,
            'kode_cabang'         => $this->recruitment->kode_cabang,
            'url'                 => route('recruitment.index'),
        ];
    }
}
