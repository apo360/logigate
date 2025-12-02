<?php

// SendTaskNotification (simplified)


namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\TaskNotification;
use Illuminate\Support\Facades\Log;


class SendTaskNotification implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    use \Illuminate\Foundation\Bus\Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $notificationId;


    public function __construct($notificationId)
    {
        $this->notificationId = $notificationId;
    }


    public function handle()
    {
        $notification = TaskNotification::find($this->notificationId);
        if (!$notification) return;


        // TODO: integrate with Mail / WhatsApp / SMS
        // For now mark as sent
        $notification->status = 'sent';
        $notification->sent_at = now();
        $notification->save();


        Log::info('Notification sent: ' . $notification->id);
    }
}
