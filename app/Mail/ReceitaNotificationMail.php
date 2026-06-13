<?php

namespace App\Mail;

use App\Models\Receita;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReceitaNotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public Receita $receita,
        public string $action,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Receita '.$this->actionLabel().': '.$this->receita->nome,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.receita-notificacao',
            with: [
                'receita' => $this->receita,
                'actionLabel' => $this->actionLabel(),
            ],
        );
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'deleted' => 'excluida',
            'updated' => 'atualizada',
            default => 'criada',
        };
    }
}
