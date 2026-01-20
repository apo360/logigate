<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Empresa;

class SubscricaoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Adicionar métodos ao modelo Empresa
        Empresa::macro('subscricaoAtiva', function() {
            return $this->subscricoes()
                       ->where('status', 'ativa')
                       ->where('data_expiracao', '>', now())
                       ->latest()
                       ->first();
        });

        Empresa::macro('temSubscricaoAtiva', function() {
            return (bool) $this->subscricaoAtiva();
        });

        Empresa::macro('modulosAtivos', function() {
            $subscricao = $this->subscricaoAtiva();
            
            if ($subscricao) {
                return $subscricao->activatedModules()
                                 ->with('module')
                                 ->where('active', true)
                                 ->get()
                                 ->pluck('module');
            }
            
            // Fallback para sistema antigo
            return $this->activatedModules()
                       ->with('module')
                       ->where('active', true)
                       ->get()
                       ->pluck('module');
        });
    }
}