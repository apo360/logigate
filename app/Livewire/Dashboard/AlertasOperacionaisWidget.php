<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardOperationalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class AlertasOperacionaisWidget extends Component
{
    public array $alerts = [];

    public function mount(): void
    {
        $empresa = Auth::user()?->empresas()->first();

        if (! $empresa) {
            return;
        }

        $this->alerts = Cache::remember("dashboard.widgets.alertas.{$empresa->id}", 60, function () use ($empresa) {
            return (new DashboardOperationalService($empresa))->getOperationalAlerts();
        });
    }

    public function render()
    {
        return view('livewire.dashboard.alertas-operacionais-widget');
    }
}
