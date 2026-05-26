<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerifyEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $email;
    public string $username;
    public string $verifyUrl;

    public function __construct(string $email, string $username, string $verifyUrl)
    {
        $this->email     = $email;
        $this->username  = $username;
        $this->verifyUrl = $verifyUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Activez votre compte Bailleurnet');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verify_email',
            with: [
                'email'     => $this->email,
                'username'  => $this->username,
                'verifyUrl' => $this->verifyUrl,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
