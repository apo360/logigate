<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardFinancialService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class TopClientesWidget extends Component
{
    use \App\Livewire\Concerns\RequiresActiveEmpresa;

    public array $topClientes = [];
    public array $clientesComDivida = [];

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
        $empresa = \App\Support\TenantContext::empresa();

        if (! $empresa) {
            $this->topClientes = [];
            $this->clientesComDivida = [];

            return;
        }

        if ($fresh) {
            Cache::forget("dashboard.widgets.top-clientes.{$empresa->id}");
        }

        $payload = Cache::remember("dashboard.widgets.top-clientes.{$empresa->id}", 120, function () use ($empresa) {
            $service = new DashboardFinancialService($empresa);

            return [
                'topClientes' => $service->getTopClientes(),
                'clientesComDivida' => $service->getClientesComDivida(),
            ];
        });

        $this->topClientes = $payload['topClientes'];
        $this->clientesComDivida = $payload['clientesComDivida'];
    }

    public function render()
    {
        return view('livewire.dashboard.top-clientes-widget');
    }
}
