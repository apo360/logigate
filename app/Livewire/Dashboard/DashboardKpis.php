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
        $empresa = Auth::user()?->empresas()->first();

        if (! $empresa) {
            $this->kpis = [];

            return;
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
