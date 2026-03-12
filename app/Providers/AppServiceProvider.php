<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Restrict log access to privileged administrators only.
        Gate::define('viewLogs', function (User $user): bool {
            return $user->hasAnyRole(['Administrador', 'Admin', 'admin'])
                || $user->getAllPermissions()->contains('name', 'viewLogs');
        });

        \App\Models\ProductPrice::observe(\App\Observers\ProductPriceObserver::class);
        \App\Models\Produto::observe(\App\Observers\ProductObserver::class);
        \App\Models\SalesInvoice::observe(\App\Observers\DocumentoObserver::class);
        \App\Models\ContaCorrente::observe(\App\Observers\ContaCorrenteObserver::class);
    }
}
