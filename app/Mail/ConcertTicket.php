<?php

namespace App\Mail;

use App\Models\ConcertTicket as ConcertTicketModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Process\Process;

class ConcertTicket extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public string $user_email;
    public string $user_name;
    public ?ConcertTicketModel $ticket;
    public array $eventData;

    public function __construct(
        string $user_email,
        string $user_name,
        ?ConcertTicketModel $ticket = null,
        array $eventData = []
    ) {
        $this->user_email = $user_email;
        $this->user_name = $user_name;
        $this->ticket = $ticket;
        $this->eventData = $eventData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎟️ Konfirmasi E-Ticket Konser - NEON ECHOES FESTIVAL 2026',
            from: config('mail.from.address', env('MAIL_FROM_ADDRESS')),
            to: $this->user_email,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            htmlString: $this->renderVueTemplate(),
        );
    }

    private function renderVueTemplate(): string
    {
        $process = new Process(
            ['node', base_path('resources/js/email/render-email.mjs')],
            base_path(),
            null,
            json_encode([
                'userName' => $this->user_name,
                'userEmail' => $this->user_email,
                'ticket' => $this->ticket?->toArray(),
                'eventData' => $this->eventData,
                'portalUrl' => route('ticket.show', ['bookingCode' => $this->ticket?->booking_code]),
            ], JSON_THROW_ON_ERROR),
        );
        $process->mustRun();

        return $process->getOutput();
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
