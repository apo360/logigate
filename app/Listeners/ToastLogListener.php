<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class ToastLogListener
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
    public function handle(object $event): void
    {
        if (is_array($event)) {
            $type = $event['type'] ?? 'info';
            $message = $event['message'] ?? 'Toast sem mensagem';

            match ($type) {
                'success' => Log::info("✅ {$message}"),
                'warning' => Log::warning("⚠️ {$message}"),
                'error', 'danger' => Log::error("❌ {$message}"),
                default => Log::info("ℹ️ {$message}"),
            };
        }
    }
}
