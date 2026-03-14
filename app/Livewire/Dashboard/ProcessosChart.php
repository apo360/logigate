<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardOperationalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class ProcessosChart extends Component
{
    public array $statusChart = [];
    public array $recentActivity = [];

    public function mount(): void
    {
        $empresa = Auth::user()?->empresas()->first();

        if (! $empresa) {
            return;
        }

        $payload = Cache::remember("dashboard.widgets.processos.{$empresa->id}", 60, function () use ($empresa) {
            $service = new DashboardOperationalService($empresa);

            return [
                'statusChart' => $service->getProcessStatusChart(),
                'recentActivity' => $service->getRecentActivity(),
            ];
        });

        $this->statusChart = $payload['statusChart'];
        $this->recentActivity = $payload['recentActivity'];
    }

    public function render()
    {
        return view('livewire.dashboard.processos-chart');
    }
}
