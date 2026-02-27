<?php

namespace App\Listeners;

use App\Actions\Subscriptions\ActivateSubscriptionAction;
use App\Events\SubscriptionActivated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ActivateSubscription
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SubscriptionActivated $event): void
    {
        app(ActivateSubscriptionAction::class)
            ->execute($event->subscription);
    }
}
