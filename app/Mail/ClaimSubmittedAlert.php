<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClaimSubmittedAlert extends Mailable
{
    use Queueable, SerializesModels;
    public $memberName; 
    public $claimType;
    public $claimNumber;
    public $amount;
    public $submissionDate;
    public $url;
    /**
     * Create a new message instance.
     */
    public function __construct( $claim )
    {
        $this->memberName = $claim['memberName']; 
        $this->claimType = $claim['claimType']; 
        $this->claimNumber = $claim['claimNumber'];
        $this->amount = $claim['amount'];
        $this->submissionDate = $claim['submissionDate'];
        $this->url = $claim['url'];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Claim Submitted Alert',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.new-submitted-claim-alert',
        );
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
