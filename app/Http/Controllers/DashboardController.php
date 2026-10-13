<?php

namespace App\Http\Controllers;

use App\Models\Licenciamento;
use App\Models\Processo;
use App\Services\Dashboard\OperationalAnalytics;
use App\Services\Dashboard\OperationalFilters;
use App\Services\Dashboard\OperationalReport;
use App\Services\DashboardQueryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

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

    public function licenciamentoEstatisticas(Request $request)
    {
        return $this->operational($request, 'licenses');
    }

    public function ProcessosEstatisticas(Request $request)
    {
        return $this->operational($request, 'processes');
    }

    private function operational(Request $request, string $kind)
    {
        Gate::authorize('viewAny', $kind === 'processes' ? Processo::class : Licenciamento::class);
        $filters = OperationalFilters::fromRequest($request);
        $service = app(OperationalAnalytics::class);
        $empresaId = (int) $this->empresa->id;
        $options = $service->options($kind, $empresaId, $filters);
        foreach (['customer', 'type', 'transport', 'country', 'office'] as $dimension) {
            if (isset($filters->values[$dimension]) && ! collect($options[$dimension])->contains(fn ($item) => (string) $item->value === (string) $filters->values[$dimension])) {
                throw ValidationException::withMessages([$dimension => 'Seleccione um valor disponível na empresa activa.']);
            }
        }
        $analytics = $kind === 'processes'
            ? $this->dashboardQueryService->processoStats($empresaId, $filters)
            : $this->dashboardQueryService->licenciamentoStats($empresaId, $filters);
        $table = $filters->values['table'] ?? 'oldest';
        $query = $service->rows($kind, $empresaId, $filters, $table);
        $sort = $filters->values['sort'] ?? ($table === 'clients' ? 'count' : 'date');
        $groupTable = in_array($table, ['clients', 'country', 'destination', 'transport', 'office', 'port']);
        $column = $groupTable ? match ($sort) {
            'name' => 'name', default => 'entries'
        } : match ($sort) {
            'number' => 'number', 'age' => 'age', default => 'date'
        };
        $rows = $query->orderBy($column, $filters->values['direction'] ?? ($groupTable ? 'desc' : 'asc'));
        if (! $groupTable) {
            $rows->orderBy('r.id');
        } else {
            $rows->orderBy('name');
        }
        $rows = $rows->paginate(15)->withQueryString();
        $reportTypes = OperationalReport::catalog($kind);

        return view($kind === 'processes' ? 'dashboard_processos' : 'dashboard_licenciamento', compact('analytics', 'filters', 'options', 'rows', 'table', 'reportTypes', 'groupTable'));
    }

    // Estatísticas de Faturação - Controller
    public function FacturaEstatisticas()
    {
        $ano = request()->input('ano', Carbon::now()->year); // Pega o ano do request ou o ano atual

        return view('dashboard_factura', $this->dashboardQueryService->facturaStats($this->empresa->id, (int) $ano));
    }
}
