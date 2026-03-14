<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardCustomsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class MercadoriasChart extends Component
{
    public array $hsCodes = [];
    public array $goodsMetrics = [];

    public function mount(): void
    {
        $empresa = Auth::user()?->empresas()->first();

        if (! $empresa) {
            return;
        }

        $payload = Cache::remember("dashboard.widgets.mercadorias.{$empresa->id}", 120, function () use ($empresa) {
            $service = new DashboardCustomsService($empresa);

            return [
                'hsCodes' => $service->getHsCodeStatistics(),
                'goodsMetrics' => $service->getGoodsMetrics(),
            ];
        });

        $this->hsCodes = $payload['hsCodes'];
        $this->goodsMetrics = $payload['goodsMetrics'];
    }

    public function render()
    {
        return view('livewire.dashboard.mercadorias-chart');
    }
}
