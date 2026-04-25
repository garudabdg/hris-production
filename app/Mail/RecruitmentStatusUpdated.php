<?php

namespace App\Mail;

use App\Models\Recruitment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecruitmentStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public Recruitment $recruitment;

    public function __construct(Recruitment $recruitment)
    {
        $this->recruitment = $recruitment;
    }

    public function envelope(): Envelope
    {
        $statusLabel = [
            'pending'   => 'Pending',
            'review'    => 'Sedang Ditinjau',
            'interview' => 'Undangan Interview',
            'offering'  => 'Penawaran Kerja',
            'diterima'  => 'Selamat! Anda Diterima',
            'ditolak'   => 'Informasi Hasil Seleksi',
        ][$this->recruitment->status] ?? 'Update Status Lamaran';

        return new Envelope(
            subject: '[' . config('app.name') . '] ' . $statusLabel . ' - ' . $this->recruitment->kode_recruitment,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recruitment.status_updated',
            with: ['r' => $this->recruitment],
        );
    }
}
