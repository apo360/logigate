<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardCustomsService;
use App\Services\Dashboard\DashboardFinancialService;
use App\Services\Dashboard\DashboardOperationalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class DashboardKpis extends Component
{
    public array $kpis = [];

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
            $this->kpis = [];

            return;
        }

        if ($fresh) {
            Cache::forget("dashboard.widgets.kpis.{$empresa->id}");
        }

        $this->kpis = Cache::remember("dashboard.widgets.kpis.{$empresa->id}", 60, function () use ($empresa) {
            $operational = new DashboardOperationalService($empresa);
            $financial = new DashboardFinancialService($empresa);
            $customs = new DashboardCustomsService($empresa);

            return [
                ...$operational->getOperationalKpis(),
                ...$financial->getFinancialKpis(),
                ...$customs->getCustomsKpis(),
            ];
        });
    }

    public function render()
    {
        return view('livewire.dashboard.dashboard-kpis');
    }
}
