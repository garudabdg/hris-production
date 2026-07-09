<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewAdminAccountMail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailStr;
    public $passwordStr;
    public $loginUrl;
    public $name;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $emailStr, $passwordStr, $loginUrl)
    {
        $this->name = $name;
        $this->emailStr = $emailStr;
        $this->passwordStr = $passwordStr;
        $this->loginUrl = $loginUrl;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Informasi Akun Admin Baru',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new_admin_account',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
