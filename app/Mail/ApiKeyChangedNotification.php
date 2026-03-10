<?php

namespace App\Mail;

use App\Models\PaymentGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApiKeyChangedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public PaymentGateway $gateway;
    public string $timeChanged;
    public string $ipAddress;

    /**
     * Create a new message instance.
     */
    public function __construct(PaymentGateway $gateway, string $ipAddress)
    {
        $this->gateway = $gateway;
        $this->timeChanged = now()->setTimezone('Africa/Dar_es_Salaam')->format('d M Y, H:i') . ' EAT';
        $this->ipAddress = $ipAddress;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ API Key Changed — ' . $this->gateway->display_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.api-key-changed',
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
