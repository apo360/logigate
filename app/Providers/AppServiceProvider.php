<?php

namespace App\Providers;

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
        \App\Models\ProductPrice::observe(\App\Observers\ProductPriceObserver::class);
        \App\Models\Produto::observe(\App\Observers\ProductObserver::class);
        \App\Models\SalesInvoice::observe(\App\Observers\DocumentoObserver::class);
        \App\Models\ContaCorrente::observe(\App\Observers\ContaCorrenteObserver::class);
    }
}
