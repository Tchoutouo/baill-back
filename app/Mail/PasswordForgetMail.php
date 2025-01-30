<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordForgetMail extends Mailable
{
    use Queueable, SerializesModels;
   
    public string $email;
    public string $newpassword;
    /**
     * Create a new message instance.
     */
    public function __construct(string $email,string $newpassword)
    {
        $this->email = $email;
        $this->newpassword = $newpassword;
    }

    /**
     * Définit l'enveloppe du message.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Réinitialisation de votre mot de passe',
        );
    }

    /**
     * Définit le contenu du message.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.password_forget',
            with: [
                'email' => $this->email,
                'email' => $this->newpassword,
            ],
        );
    }

    /**
     * Définit les pièces jointes du message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
