<?php

namespace App\Services\Dashboard;

use App\Models\EmolumentoTarifa;
use App\Models\Processo;
use App\Models\SalesInvoice;
use Illuminate\Support\Facades\DB;

class DashboardForecastService extends BaseDashboardService
{
    /**
     * Keep forecast outputs structured so future AI/trend modules can consume
     * the same time-series and projection payloads without changing widgets.
     */
    public function getRevenueForecast(): array
    {
        $empresaId = $this->empresaId();

        $receitaAberta = (float) Processo::query()
            ->where('empresa_id', $empresaId)
            ->where(function ($query) {
                $query->whereNull('DataFecho')
                    ->orWhereNotIn(DB::raw('LOWER(Estado)'), ['concluido', 'concluído', 'fechado', 'finalizado']);
            })
            ->sum('ValorTotal');

        $mediaMensal = (float) SalesInvoice::query()
            ->join('sales_document_totals', 'sales_document_totals.documentoID', '=', 'sales_invoice.id')
            ->where('sales_invoice.empresa_id', $empresaId)
            ->whereDate('sales_invoice.invoice_date', '>=', now()->startOfMonth()->subMonths(5))
            ->selectRaw('AVG(sales_document_totals.gross_total) as media')
            ->value('media');

        return [
            'receita_prevista' => round($receitaAberta, 2),
            'baseline_mensal' => round($mediaMensal, 2),
            'potencial_total' => round($receitaAberta + $mediaMensal, 2),
        ];
    }

    public function getPredictedDuties(): array
    {
        $empresaId = $this->empresaId();

        $direitos = (float) EmolumentoTarifa::query()
            ->whereHas('processo', function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId)
                    ->where(function ($inner) {
                        $inner->whereNull('DataFecho')
                            ->orWhereNotIn(DB::raw('LOWER(Estado)'), ['concluido', 'concluído', 'fechado', 'finalizado']);
                    });
            })
            ->sum('direitos');

        $iva = (float) EmolumentoTarifa::query()
            ->whereHas('processo', function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId)
                    ->where(function ($inner) {
                        $inner->whereNull('DataFecho')
                            ->orWhereNotIn(DB::raw('LOWER(Estado)'), ['concluido', 'concluído', 'fechado', 'finalizado']);
                    });
            })
            ->sum('iva_aduaneiro');

        return [
            'direitos_previstos' => round($direitos, 2),
            'iva_previsto' => round($iva, 2),
        ];
    }

    public function getOpenProcessWorkload(): array
    {
        $rows = Processo::query()
            ->selectRaw('YEAR(DataAbertura) as year, WEEK(DataAbertura, 1) as week, COUNT(*) as total')
            ->where('empresa_id', $this->empresaId())
            ->where(function ($query) {
                $query->whereNull('DataFecho')
                    ->orWhereNotIn(DB::raw('LOWER(Estado)'), ['concluido', 'concluído', 'fechado', 'finalizado']);
            })
            ->whereDate('DataAbertura', '>=', now()->subWeeks(8))
            ->groupByRaw('YEAR(DataAbertura), WEEK(DataAbertura, 1)')
            ->orderByRaw('YEAR(DataAbertura), WEEK(DataAbertura, 1)')
            ->get();

        return [
            'labels' => $rows->map(fn ($row) => sprintf('S%02d/%s', $row->week, substr((string) $row->year, -2)))->all(),
            'series' => $rows->pluck('total')->map(fn ($value) => (int) $value)->all(),
        ];
    }

    public function getTrendSignals(): array
    {
        return [
            'revenue_forecast' => $this->getRevenueForecast(),
            'duties_projection' => $this->getPredictedDuties(),
            'workload_forecast' => $this->getOpenProcessWorkload(),
        ];
    }
}
