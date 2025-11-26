<?php

namespace App\Notifications;

use App\Models\Produto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PriceReevaluationResultNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Produto $produto;
    protected float $precoAntigo;
    protected float $precoAtual;
    protected float $variacao;
    protected string $recomendacao;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        Produto $produto,
        float $precoAntigo,
        float $precoAtual,
        float $variacao,
        string $recomendacao
    ) {
        $this->produto      = $produto;
        $this->precoAntigo  = $precoAntigo;
        $this->precoAtual   = $precoAtual;
        $this->variacao     = $variacao;
        $this->recomendacao = $recomendacao;
    }

    /**
     * Define canais: email + database
     */
    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Email em Markdown
     */
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reavaliação de Preço — Produto #' . $this->produto->id)
            ->markdown('emails.prices.reevaluation', [
                'produto'      => $this->produto,
                'precoAntigo'  => $this->precoAntigo,
                'precoAtual'   => $this->precoAtual,
                'variacao'     => $this->variacao,
                'recomendacao' => $this->recomendacao
            ]);
    }

    /**
     * Armazenamento no banco (notifications table)
     */
    public function toDatabase($notifiable): array
    {
        return [
            'produto_id'   => $this->produto->id,
            'nome'          => $this->produto->ProductDescription,
            'preco_antigo'  => $this->precoAntigo,
            'preco_atual'   => $this->precoAtual,
            'variacao'      => round($this->variacao, 2),
            'recomendacao'  => $this->recomendacao,
            'timestamp'     => now()->toDateTimeString()
        ];
    }
}
