<?php

namespace App\Notifications;

use App\Models\Produto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Produto $produto;
    protected float $oldPrice;
    protected float $newPrice;
    protected string $impacto;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        Produto $produto,
        float $oldPrice,
        float $newPrice,
        string $impacto
    ) {
        $this->produto  = $produto;
        $this->oldPrice = $oldPrice;
        $this->newPrice = $newPrice;
        $this->impacto  = $impacto;
    }

    /**
     * Define canais: email + database.
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Email em formato Markdown.
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Atualização de Preço — Produto #' . $this->produto->id)
            ->markdown('emails.prices.updated', [
                'produto'  => $this->produto,
                'oldPrice' => $this->oldPrice,
                'newPrice' => $this->newPrice,
                'impacto'  => $this->impacto
            ]);
    }

    /**
     * Armazenamento no banco para auditoria.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'produto_id'  => $this->produto->id,
            'nome'        => $this->produto->ProductDescription,
            'preco_antigo'=> $this->oldPrice,
            'preco_novo'  => $this->newPrice,
            'impacto'     => $this->impacto,
            'timestamp'   => now()->toDateTimeString()
        ];
    }
}
