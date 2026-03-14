<?php

namespace App\Livewire\Dashboard;

use App\Services\Dashboard\DashboardCustomsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class DireitosAduaneirosWidget extends Component
{
    public array $summary = [];

    public function mount(): void
    {
        $empresa = Auth::user()?->empresas()->first();

        if (! $empresa) {
            return;
        }

        $this->summary = Cache::remember("dashboard.widgets.direitos.{$empresa->id}", 120, function () use ($empresa) {
            return (new DashboardCustomsService($empresa))->getCustomsDutiesSummary();
        });
    }

    public function render()
    {
        return view('livewire.dashboard.direitos-aduaneiros-widget');
    }
}
