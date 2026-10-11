<?php

namespace App\Notifications;

use App\Models\Subscricao;
use Illuminate\Notifications\Notification;

class SubscriptionRenewalNotice extends Notification
{
    public function __construct(private Subscricao $subscription) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return ['type' => 'subscription_renewal', 'empresa_id' => $this->subscription->empresa_id,
            'title' => 'Renovação da subscrição', 'message' => 'O plano expira em '.$this->subscription->data_expiracao->format('d/m/Y').'. Renove para manter o acesso.',
            'url' => route('billing.plans'), 'subscription_id' => $this->subscription->id];
    }
}
