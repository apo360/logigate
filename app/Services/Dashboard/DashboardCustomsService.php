<?php

namespace App\Services\Dashboard;

use App\Models\EmolumentoTarifa;
use App\Models\Mercadoria;
use App\Models\Processo;
use Illuminate\Support\Facades\DB;

class DashboardCustomsService extends BaseDashboardService
{
    public function getCustomsKpis(): array
    {
        $empresaId = $this->empresaId();

        return [
            'direitos_aduaneiros_pagos' => (float) EmolumentoTarifa::query()
                ->whereHas('processo', fn ($query) => $query->where('empresa_id', $empresaId))
                ->sum('direitos'),
            'iva_aduaneiro_total' => (float) EmolumentoTarifa::query()
                ->whereHas('processo', fn ($query) => $query->where('empresa_id', $empresaId))
                ->sum('iva_aduaneiro'),
            'mercadorias_registadas' => Mercadoria::query()
                ->whereHas('processos', fn ($query) => $query->where('empresa_id', $empresaId))
                ->count(),
            'valor_importado_total' => (float) Processo::where('empresa_id', $empresaId)->sum('ValorTotal'),
        ];
    }

    public function getHsCodeStatistics(int $limit = 8): array
    {
        $rows = Mercadoria::query()
            ->join('processos', 'processos.id', '=', 'mercadorias.Fk_Importacao')
            ->leftJoin('pauta_aduaneira', 'pauta_aduaneira.codigo', '=', 'mercadorias.codigo_aduaneiro')
            ->where('processos.empresa_id', $this->empresaId())
            ->selectRaw('COALESCE(mercadorias.codigo_aduaneiro, "Sem codigo") as codigo')
            ->selectRaw('COALESCE(MAX(pauta_aduaneira.descricao), "Sem descricao") as descricao')
            ->selectRaw('COUNT(*) as total_itens')
            ->selectRaw('SUM(COALESCE(mercadorias.preco_total, 0)) as valor_total')
            ->groupBy('mercadorias.codigo_aduaneiro')
            ->orderByDesc('valor_total')
            ->limit($limit)
            ->get();

        return [
            'labels' => $rows->pluck('codigo')->all(),
            'series' => $rows->pluck('valor_total')->map(fn ($value) => round((float) $value, 2))->all(),
            'rows' => $rows->map(fn ($row) => [
                'codigo' => $row->codigo,
                'descricao' => $row->descricao,
                'total_itens' => (int) $row->total_itens,
                'valor_total' => round((float) $row->valor_total, 2),
            ])->all(),
        ];
    }

    public function getGoodsMetrics(): array
    {
        return Mercadoria::query()
            ->join('processos', 'processos.id', '=', 'mercadorias.Fk_Importacao')
            ->where('processos.empresa_id', $this->empresaId())
            ->selectRaw('COUNT(*) as total_mercadorias')
            ->selectRaw('SUM(COALESCE(mercadorias.Quantidade, 0)) as quantidade_total')
            ->selectRaw('SUM(COALESCE(mercadorias.Peso, 0)) as peso_total')
            ->selectRaw('SUM(COALESCE(mercadorias.preco_total, 0)) as valor_total')
            ->first()?->toArray() ?? [
                'total_mercadorias' => 0,
                'quantidade_total' => 0,
                'peso_total' => 0,
                'valor_total' => 0,
            ];
    }

    public function getCustomsDutiesSummary(): array
    {
        $rows = EmolumentoTarifa::query()
            ->join('processos', 'processos.id', '=', 'emolumento_tarifas.processo_id')
            ->where('processos.empresa_id', $this->empresaId())
            ->selectRaw('SUM(COALESCE(direitos, 0)) as direitos')
            ->selectRaw('SUM(COALESCE(iva_aduaneiro, 0)) as iva_aduaneiro')
            ->selectRaw('SUM(COALESCE(emolumentos, 0)) as emolumentos')
            ->selectRaw('SUM(COALESCE(impostoEstatistico, 0)) as imposto_estatistico')
            ->first();

        return [
            'direitos' => round((float) ($rows->direitos ?? 0), 2),
            'iva_aduaneiro' => round((float) ($rows->iva_aduaneiro ?? 0), 2),
            'emolumentos' => round((float) ($rows->emolumentos ?? 0), 2),
            'imposto_estatistico' => round((float) ($rows->imposto_estatistico ?? 0), 2),
        ];
    }
}
