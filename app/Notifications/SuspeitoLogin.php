<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class SuspeitoLogin extends Notification implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $ip;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $ip)
    {
        $this->user = $user;
        $this->ip = $ip;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail']; // Você pode adicionar 'database' ou 'slack', etc.
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
        ->subject('Login suspeito detectado')
        ->view('emails.suspeito_login', [
            'userName' => $notifiable->name,
            'ipAddress' => $this->ip,
        ]);
    }
   
    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'ip' => $this->ip,
            'user_id' => $this->user->id,
        ];
    }
}
