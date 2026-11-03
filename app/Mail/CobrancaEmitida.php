<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CobrancaEmitida extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $texto,
        public string $assunto,
        public string $caminhoPdf,
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

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromStorageDisk('local', $this->caminhoPdf)
                ->as('boleto.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
