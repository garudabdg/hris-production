<?php

namespace App\Notifications;

use App\Models\ItTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewItTicketNotification extends Notification
{
    use Queueable;

    public function __construct(protected ItTicket $ticket) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $prioritasLabel = match($this->ticket->prioritas) {
            'critical' => '🔴 Critical',
            'high'     => '🟠 High',
            'medium'   => '🟡 Medium',
            'low'      => '🟢 Low',
            default    => $this->ticket->prioritas,
        };

        return [
            'type'         => 'new_it_ticket',
            'title'        => "🎫 Tiket IT Baru — {$prioritasLabel}",
            'message'      => "[{$this->ticket->nomor_tiket}] {$this->ticket->judul} — diajukan oleh {$this->ticket->pemohon->name}.",
            'nomor_tiket'  => $this->ticket->nomor_tiket,
            'judul'        => $this->ticket->judul,
            'prioritas'    => $this->ticket->prioritas,
            'kategori'     => $this->ticket->kategori,
            'pemohon'      => $this->ticket->pemohon->name,
            'kode_cabang'  => $this->ticket->kode_cabang,
            'url'          => route('it-ticket.show', $this->ticket->id),
        ];
    }
}
