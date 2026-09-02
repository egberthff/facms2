<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimStatusUpdated extends Mailable implements ShouldQueue 
{
    use Queueable, SerializesModels;

    public array $emailData;

    public function __construct(array $emailData)
    {
        $this->emailData = $emailData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on Your Claim #' . $this->emailData['claimNumber'] . ' [' . $this->emailData['status'] . ']',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.claim-status-updated',
        );
    }
}
