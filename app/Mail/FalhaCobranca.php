<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FalhaCobranca extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $texto,
        public string $assunto,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->assunto);
    }

    public function content(): Content
    {
        return new Content(
            htmlString: nl2br(e($this->texto)),
        );
    }
}
