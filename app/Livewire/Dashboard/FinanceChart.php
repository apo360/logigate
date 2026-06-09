<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardFinancialService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class FinanceChart extends Component
{
    public array $revenue = [];
    public array $summary = [];

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
            $this->revenue = [];
            $this->summary = [];

            return;
        }

        if ($fresh) {
            Cache::forget("dashboard.widgets.finance.{$empresa->id}");
        }

        $payload = Cache::remember("dashboard.widgets.finance.{$empresa->id}", 120, function () use ($empresa) {
            $service = new DashboardFinancialService($empresa);

            return [
                'revenue' => $service->getRevenueLast12Months(),
                'summary' => $service->getPaymentsSummary(),
            ];
        });

        $this->revenue = $payload['revenue'];
        $this->summary = $payload['summary'];
    }

    public function render()
    {
        return view('livewire.dashboard.finance-chart');
    }
}
