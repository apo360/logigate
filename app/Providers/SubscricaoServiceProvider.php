<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class SubscricaoServiceProvider extends ServiceProvider
{
    // Compatibility methods now live on Empresa, where Eloquent callers can use them.
    public function boot(): void {}
}
