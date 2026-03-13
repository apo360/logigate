<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Empresa;
use App\Models\Subscricao;

class SubscricaoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Deprecated compatibility macros. Keep them aligned with the relation-based
        // implementation until the legacy subscription flow is fully removed.
        // Adicionar métodos ao modelo Empresa
        Empresa::macro('subscricaoAtiva', function() {
            return $this->subscricoes()
                       ->whereIn('status', Subscricao::activeStatuses())
                       ->where(function ($query) {
                           $query->whereNull('data_expiracao')
                               ->orWhere('data_expiracao', '>', now());
                       })
                       ->latest()
                       ->first();
        });

        Empresa::macro('temSubscricaoAtiva', function() {
            return $this->subscricaoAtiva()->exists();
        });

        Empresa::macro('modulosAtivos', function() {
            $subscricao = $this->subscricaoAtiva()->first();
            
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
