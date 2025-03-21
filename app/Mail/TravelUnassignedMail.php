<?php

namespace App\Mail;

use App\Models\Meeting;
use App\Models\Travel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TravelUnassignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $travel;
    public $meeting;
    public $user_name;

    /**
     * Create a new message instance.
     */
    public function __construct(Travel $travel, Meeting $meeting, $user_name)
    {
        $this->travel = $travel;
        $this->meeting = $meeting;
        $this->user_name = $user_name;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🚫 Travel Planner Dihapus dari Jadwal: {$this->meeting->location}"
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.travel-unassigned'
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
