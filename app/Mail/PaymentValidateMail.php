<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentValidateMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $intitule;
    public string $amount;
    public string $mode_paiement;
    public string $typeAbonnement;
    /**
     * Create a new message instance.
     */
    public function __construct(string $intitule, string $amount, string $mode_paiement, string $typeAbonnement)
    {
        //
        $this->mode_paiement = $mode_paiement;
        $this->intitule = $intitule;
        $this->amount = $amount;
        $this->typeAbonnement = $typeAbonnement;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Paiement réussi',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new_payment',
            with:[
                'mode_paiement' => $this->mode_paiement,
                'typeAbonnement' => $this->typeAbonnement,
                'intitule' => $this->intitule,
                'amount' => $this->amount,
            ]
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
