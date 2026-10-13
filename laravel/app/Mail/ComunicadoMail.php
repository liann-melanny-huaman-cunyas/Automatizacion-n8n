<?php

namespace App\Mail;

use App\Models\Comunicado;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ComunicadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Comunicado $comunicado) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->comunicado->asunto);
    }

    public function content(): Content
    {
        return new Content(
            htmlString: nl2br(e($this->comunicado->cuerpo))
        );
    }
}