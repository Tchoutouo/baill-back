<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordAdvertiser extends Mailable
{
    use Queueable, SerializesModels;
    public string $email;
    public string $firstPassword;
    public string $name;

    /**
     * Create a new message instance.
     */
    public function __construct(string $email, string $firstPassword, $name)
    {
        $this->email = $email;
        $this->name = $name;
        $this->firstPassword = $firstPassword;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Identifiants de connexion',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password_advertiser',
            with: [
                'email' => $this->email,
                'name' => $this->name,
                'firstPassword' => $this->firstPassword,
            ],
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
