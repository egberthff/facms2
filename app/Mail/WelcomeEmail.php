<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable implements ShouldQueue 
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $password;
    public $url;

    // Only pass the required unique arguments
    public function __construct($name, $email)
    {
        $this->name = $name;
        $this->email = $email;
        $this->password = 'password123';  
        $this->url = config('app.url') . '/forgot-password'; 
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome Email',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.welcome',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
