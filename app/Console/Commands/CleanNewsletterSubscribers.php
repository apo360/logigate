<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\NewsletterSubscriber;

class CleanNewsletterSubscribers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsletter:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove subscribers pending for more than 48 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = NewsletterSubscriber::where('status', 'pending')
            ->where('created_at', '<', now()->subHours(48))
            ->delete();

        $this->info("Removed {$count} pending subscribers.");
    }
}
