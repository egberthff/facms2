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
        // Normalize immediately on construction to prevent string matching issues
        $this->recipientType = strtolower(trim($recipientType));
        $this->emailData = $emailData;
    }

    public function envelope(): Envelope
    {
        $claimNumber = $this->emailData['claimNumber'] ?? '';
        $status = $this->emailData['status'] ?? '';

        // Clean mapping for subjects based on recipient types
        $subjects = [
            'member'    => "Update on Your Claim #{$claimNumber} [{$status}]",
            'treasurer' => "Disbursement Notice on Claim #{$claimNumber}",
            'president' => "Disbursement Alert on Claim #{$claimNumber}",
        ];

        // Fallback to member subject if type is unrecognized
        $subject = $subjects[$this->recipientType] ?? "Claim Notification #{$claimNumber}";

        return new Envelope(
            subject: $subject,
        ); 
    }

    public function content(): Content
    {
        // Clean mapping for blade template views based on recipient types
        $views = [
            'member'    => 'emails.claim-status-updated',
            'treasurer' => 'emails.claim-treasurer-disbursement-alert',
            'president' => 'emails.claim-president-disbursement-alert', 
        ];

        // Fallback to member view if type is unrecognized
        $view = $views[$this->recipientType] ?? 'emails.claim-status-updated';

        return new Content(
            view: $view,
        );
    }
}
