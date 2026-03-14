<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardForecastService;
use App\Services\Dashboard\DashboardOperationalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class PrevisaoReceitaWidget extends Component
{
    public array $forecast = [];
    public array $duties = [];
    public array $workload = [];

    public function mount(): void
    {
        $empresa = Auth::user()?->empresas()->first();

        if (! $empresa) {
            return;
        }

        $payload = Cache::remember("dashboard.widgets.previsao.{$empresa->id}", 180, function () use ($empresa) {
            $forecast = new DashboardForecastService($empresa);
            $operational = new DashboardOperationalService($empresa);

            return [
                'forecast' => $forecast->getRevenueForecast(),
                'duties' => $forecast->getPredictedDuties(),
                'workload' => $operational->getWeeklyProcessWorkload(),
            ];
        });

        $this->forecast = $payload['forecast'];
        $this->duties = $payload['duties'];
        $this->workload = $payload['workload'];
    }

    public function render()
    {
        return view('livewire.dashboard.previsao-receita-widget');
    }
}
