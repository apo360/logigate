<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardFinancialService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class TopClientesWidget extends Component
{
    public array $topClientes = [];
    public array $clientesComDivida = [];

    public function mount(): void
    {
        $empresa = Auth::user()?->empresas()->first();

        if (! $empresa) {
            return;
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
