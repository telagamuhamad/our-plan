<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SavingTransactionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $saving;
    public $type;
    public $amount;
    public $note;
    public $user_name;

    /**
     * Create a new message instance.
     */
    public function __construct(
        $saving,
        $type,
        $amount,
        $note,
        $user_name
    )
    {
        $this->saving = $saving;
        $this->type = $type;
        $this->amount = $amount;
        $this->note = $note;
        $this->user_name = $user_name;

        $subjectSentence = '';
        if ($this->type === 'deposit') {
            $subjectSentence = '💰DEPOSIT';
        } else {
            $subjectSentence = '💸WITHDRAWAL';
        }

        $this->type = $subjectSentence;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: strtoupper($this->type) . " Tabungan: {$this->saving->name}"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.saving-transaction'
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
