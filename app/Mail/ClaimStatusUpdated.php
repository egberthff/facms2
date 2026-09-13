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
        $status = strtolower(trim($emailData['status'] ?? ''));
        $statusUpper = strtoupper($status);
    
        // 1. Handle specialized multi-step subject rules for the Campus Treasurer
        if ($this->recipientType === 'treasurer') {
            if ($status === 'approved') {
                $subject = "Action Required: Collect Contributions for Claim #{$claimNumber}";
            } elseif ($status === 'for disbursement') {
                $subject = "Go Signal: Disburse Funds for Claim #{$claimNumber}";
            } elseif ($status === 'rejected') {
                $subject = "Notice: Transaction Voided for Claim #{$claimNumber}";
            } else {
                $subject = "Treasury Update: Claim #{$claimNumber} [{$statusUpper}]";
            }
        } 
        // 2. Handle specialized subject rules for when a claim gets Rejected/Returned across roles
        elseif ($status === 'rejected') {
            $subjects = [
                'member'              => "ACTION REQUIRED: Your Claim #{$claimNumber} Has Been Returned",
                'president'           => "Audit Tracking: Claim #{$claimNumber} Rejected",
                'federated president' => "Notice: Claim #{$claimNumber} Voided",
                'federated treasurer' => "Notice: Claim #{$claimNumber} Cancelled",
            ];
            $subject = $subjects[$this->recipientType] ?? "Claim Notification #{$claimNumber}";
        } 
        // 3. Fallback to your standard progressive workflow tracking subjects
        else {
            $subjects = [
                'member'              => "Update on Your Claim #{$claimNumber} [{$statusUpper}]",
                'president'           => "Disbursement Alert on Claim #{$claimNumber}",
                'federated president' => "Federated Review Required for Claim #{$claimNumber}",
                'federated treasurer' => "Federated Processing Required for Claim #{$claimNumber}",
            ];
            $subject = $subjects[$this->recipientType] ?? "Claim Notification #{$claimNumber}";
        }
    
        return new Envelope(
            subject: $subject,
        ); 
    }

    public function content(): Content
    {
        // Mapping for blade template views based on all applicable recipient types
        $views = [
            'member'              => 'emails.claim-status-updated',
            'treasurer'           => 'emails.claim-treasurer-disbursement-alert',
            'president'           => 'emails.claim-president-disbursement-alert', 
            'federated president' => 'emails.claim-federated-president-alert', 
            'federated treasurer' => 'emails.claim-federated-treasurer-alert', 
        ];

        // Fallback to member view if type is unrecognized
        $view = $views[$this->recipientType] ?? 'emails.claim-status-updated';

        return new Content(
            view: $view,
        );
    }
}
