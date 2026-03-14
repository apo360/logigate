<?php

namespace App\Services\Dashboard;

use App\Models\Licenciamento;
use App\Models\Mercadoria;
use App\Models\Processo;
use App\Models\SalesInvoice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardOperationalService extends BaseDashboardService
{
    public function getOperationalKpis(): array
    {
        $empresaId = $this->empresaId();
        $monthStart = now()->startOfMonth();

        return [
            'processos_abertos' => Processo::where('empresa_id', $empresaId)
                ->where(function ($query) {
                    $query->whereNull('DataFecho')
                        ->orWhereNotIn(DB::raw('LOWER(Estado)'), ['concluido', 'concluído', 'fechado', 'finalizado']);
                })
                ->count(),
            'processos_concluidos_mes' => Processo::where('empresa_id', $empresaId)
                ->where(function ($query) {
                    $query->whereNotNull('DataFecho')
                        ->orWhereIn(DB::raw('LOWER(Estado)'), ['concluido', 'concluído', 'fechado', 'finalizado']);
                })
                ->where(function ($query) use ($monthStart) {
                    $query->whereDate('DataFecho', '>=', $monthStart)
                        ->orWhereDate('updated_at', '>=', $monthStart);
                })
                ->count(),
            'licenciamentos_ativos' => Licenciamento::where('empresa_id', $empresaId)
                ->whereIn('status_fatura', ['pendente', 'emitida'])
                ->count(),
            'mercadorias_registadas' => Mercadoria::query()
                ->whereHas('processos', fn ($query) => $query->where('empresa_id', $empresaId))
                ->count(),
        ];
    }

    public function getProcessStatusChart(): array
    {
        $rows = Processo::query()
            ->selectRaw('COALESCE(Estado, "Sem estado") as label, COUNT(*) as total')
            ->where('empresa_id', $this->empresaId())
            ->groupBy('Estado')
            ->orderByDesc('total')
            ->get();

        return [
            'labels' => $rows->pluck('label')->all(),
            'series' => $rows->pluck('total')->map(fn ($value) => (int) $value)->all(),
            'rows' => $rows->map(fn ($row) => ['label' => $row->label, 'total' => (int) $row->total])->all(),
        ];
    }

    public function getRecentActivity(int $limit = 5): array
    {
        return [
            'processos' => Processo::query()
                ->select('id', 'NrProcesso', 'Estado', 'DataAbertura', 'customer_id', 'created_at')
                ->with('cliente:id,CompanyName')
                ->where('empresa_id', $this->empresaId())
                ->latest('created_at')
                ->limit($limit)
                ->get()
                ->map(fn (Processo $processo) => [
                    'id' => $processo->id,
                    'codigo' => $processo->NrProcesso,
                    'estado' => $processo->Estado,
                    'cliente' => $processo->cliente?->CompanyName,
                    'data' => optional($processo->created_at)->format('d/m/Y H:i'),
                ])
                ->all(),
            'licenciamentos' => Licenciamento::query()
                ->select('id', 'codigo_licenciamento', 'status_fatura', 'cliente_id', 'created_at')
                ->with('cliente:id,CompanyName')
                ->where('empresa_id', $this->empresaId())
                ->latest('created_at')
                ->limit($limit)
                ->get()
                ->map(fn (Licenciamento $licenciamento) => [
                    'id' => $licenciamento->id,
                    'codigo' => $licenciamento->codigo_licenciamento,
                    'estado' => $licenciamento->status_fatura,
                    'cliente' => $licenciamento->cliente?->CompanyName,
                    'data' => optional($licenciamento->created_at)->format('d/m/Y H:i'),
                ])
                ->all(),
        ];
    }

    public function getOperationalAlerts(int $limit = 6): array
    {
        $empresaId = $this->empresaId();

        $processosSemFactura = Processo::query()
            ->select('id', 'NrProcesso', 'Estado', 'created_at')
            ->where('empresa_id', $empresaId)
            ->whereDoesntHave('procLicenFaturas')
            ->latest('created_at')
            ->limit($limit)
            ->get();

        $licenciamentosPendentes = Licenciamento::query()
            ->select('id', 'codigo_licenciamento', 'status_fatura', 'created_at')
            ->where('empresa_id', $empresaId)
            ->where('status_fatura', 'pendente')
            ->latest('created_at')
            ->limit($limit)
            ->get();

        return [
            'processos_sem_factura' => $processosSemFactura->map(fn (Processo $processo) => [
                'id' => $processo->id,
                'codigo' => $processo->NrProcesso,
                'estado' => $processo->Estado,
                'data' => optional($processo->created_at)->format('d/m/Y'),
            ])->all(),
            'licenciamentos_pendentes' => $licenciamentosPendentes->map(fn (Licenciamento $licenciamento) => [
                'id' => $licenciamento->id,
                'codigo' => $licenciamento->codigo_licenciamento,
                'estado' => $licenciamento->status_fatura,
                'data' => optional($licenciamento->created_at)->format('d/m/Y'),
            ])->all(),
        ];
    }

    public function getWeeklyProcessWorkload(int $weeks = 8): array
    {
        $rows = Processo::query()
            ->selectRaw('YEAR(DataAbertura) as year, WEEK(DataAbertura, 1) as week, COUNT(*) as total')
            ->where('empresa_id', $this->empresaId())
            ->whereDate('DataAbertura', '>=', now()->subWeeks($weeks))
            ->groupByRaw('YEAR(DataAbertura), WEEK(DataAbertura, 1)')
            ->orderByRaw('YEAR(DataAbertura), WEEK(DataAbertura, 1)')
            ->get();

        return [
            'labels' => $rows->map(fn ($row) => sprintf('S%02d/%s', $row->week, substr((string) $row->year, -2)))->all(),
            'series' => $rows->pluck('total')->map(fn ($value) => (int) $value)->all(),
        ];
    }
}
