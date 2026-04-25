<?php

namespace App\Mail;

use App\Models\Recruitment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecruitmentConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public Recruitment $recruitment;

    public function __construct(Recruitment $recruitment)
    {
        $this->recruitment = $recruitment;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Konfirmasi Lamaran Kerja - ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recruitment.confirmation',
            with: ['r' => $this->recruitment],
        );
    }
}
