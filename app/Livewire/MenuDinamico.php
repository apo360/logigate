<?php

namespace App\Livewire;

use App\Application\Integracoes\Services\IntegracaoResolverService;
use App\Livewire\Concerns\RequiresActiveEmpresa;
use App\Services\EffectiveModuleResolver;
use App\Services\NavigationResolver;
use App\Support\TenantContext;
use Livewire\Component;

class MenuDinamico extends Component
{
    use RequiresActiveEmpresa;

    public $modulosAtivos = [];
    public $menusPrincipais = [];
    public $menusPorModulo = [];
    public $nomesModulos = [];
    public $facturacaoHongayetuActiva = false;
    public $navigationGroups = [];

    public function render()
    {
        // Recompute after hydration: never trust a stale permission/tenant snapshot.
        $empresa = TenantContext::empresa();
        $actor = auth()->user();
        $this->navigationGroups = $this->menusPrincipais = [];
        if ($empresa && $actor) {
            $this->modulosAtivos = app(EffectiveModuleResolver::class)->ids((int) $empresa->id);
            $navigation = app(NavigationResolver::class);
            $this->menusPrincipais = $navigation->dynamic($actor, $this->modulosAtivos);
            $this->facturacaoHongayetuActiva = app(IntegracaoResolverService::class)->isFacturacaoHongayetuActiva($empresa->id);
            $this->navigationGroups = $navigation->groups($actor, $this->menusPrincipais, $this->facturacaoHongayetuActiva);
        }
        return view('livewire.menu-dinamico');
    }
}