<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminLoginNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $loginTime;
    public string $ipAddress;
    public string $userAgent;

    /**
     * Create a new message instance.
     */
    public function __construct(string $ipAddress, string $userAgent)
    {
        $this->loginTime = now()->setTimezone('Africa/Dar_es_Salaam')->format('d M Y, H:i') . ' EAT';
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔐 Admin Login Alert — ' . config('app.name'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-login',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
