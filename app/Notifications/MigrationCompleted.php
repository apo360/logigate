<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MigrationCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    protected $type;
    protected $empresa;

    /**
     * Create a new notification instance.
     */
 
     public function __construct($type, $empresa)
     {
         $this->type = $type;
         $this->empresa = $empresa;
     }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->line('A importação de ' . $this->type . ' foi concluída.')
                    ->action('Verificar', url('/'))
                    ->line('Obrigado por usar nossa aplicação!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
