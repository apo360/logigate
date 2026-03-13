<?php

namespace App\Services;

use App\Models\Licenciamento;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LicenciamentoReportService
{
    public function forType(string $tipo, int $empresaId, string $dataInicio, string $dataFim)
    {
        return Cache::remember(
            'relatorio:licenciamento:' . md5(json_encode([$tipo, $empresaId, $dataInicio, $dataFim])),
            now()->addMinutes(10),
            fn () => match ($tipo) {
                'cliente' => Licenciamento::select([
                    'customers.CompanyName AS Cliente',
                    'licenciamentos.tipo_declaracao AS Tipo_de_Licenciamento',
                    DB::raw('MAX(licenciamentos.created_at) AS `Última Emissão`'),
                    DB::raw('COUNT(licenciamentos.id) AS Total'),
                ])
                    ->leftJoin('customers', 'licenciamentos.cliente_id', '=', 'customers.id')
                    ->where('licenciamentos.empresa_id', $empresaId)
                    ->groupBy('customers.CompanyName', 'licenciamentos.tipo_declaracao')
                    ->orderBy('customers.CompanyName')
                    ->get(),
                'tipo' => Licenciamento::select([
                    'tipo_declaracao AS Tipo_de_Licenciamento',
                    DB::raw('COUNT(*) AS Quantidade_Total'),
                    DB::raw('ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM licenciamentos WHERE empresa_id = ' . (int) $empresaId . ')), 2) AS Percentual'),
                ])
                    ->where('licenciamentos.empresa_id', $empresaId)
                    ->groupBy('tipo_declaracao')
                    ->orderByDesc('Quantidade_Total')
                    ->get(),
                'periodo' => Licenciamento::select([
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') AS Mes_Ano"),
                    'tipo_declaracao AS Tipo_de_Licenciamento',
                    DB::raw('COUNT(*) AS Total_Licenciamentos'),
                ])
                    ->where('licenciamentos.empresa_id', $empresaId)
                    ->whereBetween('created_at', [$dataInicio, $dataFim])
                    ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"), 'tipo_declaracao')
                    ->orderBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
                    ->orderBy('tipo_declaracao')
                    ->get(),
                'localidade' => Licenciamento::select([
                    DB::raw("CONCAT(pais_origem, IF(porto_origem IS NOT NULL, CONCAT(' - ', porto_origem), '')) AS Localidade"),
                    DB::raw("CASE 
                        WHEN tipo_declaracao = 11 THEN 'Importação'
                        WHEN tipo_declaracao = 21 THEN 'Exportação'
                        ELSE 'Outro'
                    END AS Tipo_de_Licenciamento"),
                    DB::raw('COUNT(*) AS Quantidade'),
                    DB::raw('ROUND((COUNT(*) * 100) / SUM(COUNT(*)) OVER (), 2) AS Percentual'),
                ])
                    ->where('licenciamentos.empresa_id', $empresaId)
                    ->groupBy('pais_origem', 'porto_origem', 'tipo_declaracao')
                    ->orderBy('pais_origem')
                    ->orderBy('porto_origem')
                    ->orderBy('tipo_declaracao')
                    ->get(),
                default => null,
            }
        );
    }
}
