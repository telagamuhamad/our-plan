<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SavingTransferMail extends Mailable
{
    use Queueable, SerializesModels;

    public $fromSaving;
    public $toSaving;
    public $amount;
    public $user_name;

    /**
     * Create a new message instance.
     */
    public function __construct(
        $fromSaving,
        $toSaving,
        $amount,
        $user_name
    )
    {
        $this->fromSaving = $fromSaving;
        $this->toSaving = $toSaving;
        $this->amount = $amount;
        $this->user_name = $user_name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🔁 Transfer Saldo Antar Tabungan"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.saving-transfer'
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
