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
            $this->hsCodes = [];
            $this->goodsMetrics = [];

            return;
        }

        if ($fresh) {
            Cache::forget("dashboard.widgets.mercadorias.{$empresa->id}");
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
