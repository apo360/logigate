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
        $this->loadWidget();
    }

    public function refreshWidget(): void
    {
        $this->loadWidget(true);
    }

    private function loadWidget(bool $fresh = false): void
    {
        $empresa = Auth::user()?->empresas()->first();

        if (! $empresa) {
            $this->alerts = [];

            return;
        }

        if ($fresh) {
            Cache::forget("dashboard.widgets.alertas.{$empresa->id}");
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
