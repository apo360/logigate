<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class MasterRouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot(); // Certifique-se de chamar o método boot() do pai

        RateLimiter::for('verify-pin', function ($request) {
            return Limit::perMinutes(5, 3)->by($request->ip());
        });
        
        $this->map(); // Chama o método map() para carregar as rotas
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        // Carrega as rotas do Administrador Master
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/master.php'));
    }
}