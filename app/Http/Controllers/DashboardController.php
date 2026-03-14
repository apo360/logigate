<?php

namespace App\Http\Controllers;

use App\Services\DashboardQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends AuthenticatedController
{
    public function __construct(private readonly DashboardQueryService $dashboardQueryService)
    {
        parent::__construct();
    }

    public function index()
    {
        // The main SaaS dashboard now loads through isolated Livewire widgets so
        // onboarding, operational, financial, customs, and forecast panels can
        // evolve independently without impacting the legacy reporting pages.
        return view('dashboard');
    }


    public function licenciamentoEstatisticas() {
        return view('dashboard_licenciamento', $this->dashboardQueryService->licenciamentoStats($this->empresa->id));

    }

    public function ProcessosEstatisticas(){
        $this->dashboardQueryService->processoStats($this->empresa->id);

    }

    // Estatísticas de Faturação - Controller
    public function FacturaEstatisticas(){
        $ano = request()->input('ano', Carbon::now()->year); // Pega o ano do request ou o ano atual
        return view('dashboard_factura', $this->dashboardQueryService->facturaStats($this->empresa->id, (int) $ano));
    }


}
