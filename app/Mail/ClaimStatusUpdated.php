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

    public $recipientType;
    public $emailData;

    public function __construct($recipientType, array $emailData)
    {
        $this->recipientType = $recipientType;
        $this->emailData = $emailData;
    }

    public function envelope(): Envelope
    {
        $isMember = strtolower(trim($this->recipientType)) === 'member';

        $subject = $isMember 
            ? 'Update on Your Claim #' . ($this->emailData['claimNumber'] ?? '') . ' [' . ($this->emailData['status'] ?? '') . ']' 
            : 'Disbursement Notice on Claim #' . ($this->emailData['claimNumber'] ?? '');

        return new Envelope(
            subject: $subject,
        ); 
    }

    public function content(): Content
    {
        // Safe fallback logic check using trim and lowercase normalization
        $isMember = strtolower(trim($this->recipientType)) === 'member';

        return new Content(
            view: $isMember 
                ? 'emails.claim-status-updated' 
                : 'emails.claim-treasurer-disbursement-alert',
        );
    }
}
