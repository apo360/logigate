<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Licenciamento;
use App\Models\Processo;
use App\Models\SalesDocTotal;
use App\Models\SalesInvoice;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardQueryService
{
    public function overview(int $empresaId): array
    {
        return Cache::remember("dashboard:overview:{$empresaId}", now()->addMinutes(5), function () use ($empresaId) {
            $licenciamento = Licenciamento::where('empresa_id', $empresaId)->get();
            $processos = Processo::where('empresa_id', $empresaId)->get();
            $clientes = Customer::whereHas('empresas', fn ($query) => $query->where('empresas.id', $empresaId))
                ->orderBy('customers.CompanyName', 'asc')
                ->get();
            $exportadors = DB::table('exportador_empresas')
                ->join('exportadors', 'exportador_empresas.exportador_id', '=', 'exportadors.id')
                ->where('exportador_empresas.empresa_id', $empresaId)
                ->orderBy('exportadors.Exportador')
                ->select('exportadors.*')
                ->get();

            $totalFaturamento = SalesInvoice::with('salesdoctotal')
                ->where('empresa_id', $empresaId)
                ->get()
                ->sum(fn ($invoice) => $invoice->salesdoctotal->gross_total ?? 0);

            $dailyRevenue = $this->dailyRevenue(1);
            $monthlyRevenue = $this->monthlyRevenue();
            $yearlyRevenue = $this->yearlyRevenue();
            $previousYearRevenue = $this->previousYearRevenue(Carbon::now()->subYear()->year);
            $faturacaoHoje = SalesDocTotal::whereDate('created_at', Carbon::today())->sum('gross_total');
            $mesAnterior = SalesDocTotal::whereMonth('created_at', now()->month - 1)->sum('gross_total');
            $numeroFaturas = SalesInvoice::where('empresa_id', $empresaId)->get();
            $ticketMedio = $totalFaturamento / max(count($numeroFaturas), 1);
            $percentCrescimento = $mesAnterior > 0 ? (($faturacaoHoje - $mesAnterior) / $mesAnterior) * 100 : 0;

            $processesByCountries = Processo::where('empresa_id', $empresaId)
                ->join('importacao', 'importacao.processo_id', '=', 'processos.id')
                ->join('paises', 'importacao.Fk_pais_origem', '=', 'paises.id')
                ->select('paises.pais as paisss', DB::raw('count(processos.id) as total'))
                ->groupBy('paises.pais')
                ->orderByDesc('total')
                ->limit(7)
                ->get();

            $topCountries = Processo::where('empresa_id', $empresaId)
                ->join('importacao', 'processos.id', '=', 'importacao.processo_id')
                ->join('paises', 'importacao.Fk_pais_origem', '=', 'paises.id')
                ->select('paises.pais', DB::raw('count(processos.id) as total'))
                ->groupBy('paises.pais')
                ->orderBy('total', 'desc')
                ->limit(5)
                ->get();

            $processesByCustomer = Customer::join('customers_empresas', 'customers_empresas.customer_id', '=', 'customers.id')
                ->join('processos', 'processos.customer_id', '=', 'customers.id')
                ->where('customers_empresas.empresa_id', $empresaId)
                ->selectRaw('customers.CompanyName, COUNT(processos.id) as total')
                ->groupBy('customers.CompanyName')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            $processosPorEstado = Processo::select('Estado', DB::raw('count(*) as total'))
                ->where('empresa_id', $empresaId)
                ->groupBy('Estado')
                ->orderByDesc('total')
                ->get();

            return compact(
                'clientes', 'exportadors', 'processos', 'licenciamento', 'topCountries',
                'processesByCustomer', 'processesByCountries', 'totalFaturamento',
                'numeroFaturas', 'dailyRevenue', 'monthlyRevenue', 'yearlyRevenue',
                'previousYearRevenue', 'processosPorEstado', 'faturacaoHoje', 'mesAnterior',
                'ticketMedio', 'percentCrescimento'
            );
        });
    }

    public function licenciamentoStats(int $empresaId): array
    {
        return Cache::remember("dashboard:licenciamento:{$empresaId}", now()->addMinutes(5), function () use ($empresaId) {
            $licenciamentos = Licenciamento::where('empresa_id', $empresaId)->get();
            $totalLicenciamentos = $licenciamentos->count();
            $importacaoCount = $licenciamentos->where('tipo_declaracao', 11)->count();
            $exportacaoCount = $licenciamentos->where('tipo_declaracao', 21)->count();
            $mediaPesoBruto = $licenciamentos->avg('peso_bruto');
            $mediaFobTotal = $licenciamentos->avg('fob_total');
            $mediaFrete = $licenciamentos->avg('frete');
            $mediaSeguro = $licenciamentos->avg('seguro');
            $mediaCif = $licenciamentos->avg('cif');
            $varianciaPesoBruto = $licenciamentos->map(fn ($item) => pow($item->peso_bruto - $mediaPesoBruto, 2))->avg();
            $desvioPadraoPesoBruto = sqrt($varianciaPesoBruto);
            $somaStatus = $licenciamentos->groupBy('status_fatura')->map->sum('fob_total');
            $distribuicaoTransporte = $licenciamentos->groupBy('tipo_transporte')->map->count();
            $nacionalidadeTransporteGrouped = $licenciamentos->groupBy('nacionalidade_transporte')->map->count();
            $mediaVolume = $licenciamentos->avg('qntd_volume');
            $tempoMedioProcessamento = $licenciamentos->map(function ($item) {
                $entrada = Carbon::parse($item->data_entrada);
                $criado = Carbon::parse($item->created_at);
                return $entrada->diffInDays($criado);
            })->avg();
            $distribuicaoPortoEntrada = $licenciamentos->groupBy('porto_entrada')->map->count();
            $distribuicaoPortoOrigem = $licenciamentos->groupBy('porto_origem')->map->count();
            $licenciamentosFormaPagamento = $licenciamentos->groupBy('forma_pagamento')->map->count();
            $statusFaturaPercentual = $licenciamentos->groupBy('status_fatura')->map->count()->map(fn ($count) => $totalLicenciamentos > 0 ? ($count / $totalLicenciamentos) * 100 : 0);
            $licencasPorMesGrouped = $licenciamentos->groupBy(fn ($item) => Carbon::parse($item->created_at)->format('Y-m'))->map->count();
            $tempoMedioProcessamentos = [
                'importacao' => Licenciamento::where('empresa_id', $empresaId)->where('tipo_declaracao', 11)->selectRaw('AVG(DATEDIFF(created_at, data_entrada)) as tempo_medio')->first()->tempo_medio,
                'exportacao' => Licenciamento::where('empresa_id', $empresaId)->where('tipo_declaracao', 21)->selectRaw('AVG(DATEDIFF(created_at, data_entrada)) as tempo_medio')->first()->tempo_medio,
            ];
            $nacionalidadeTransporte = Licenciamento::select('nacionalidade_transporte', 'tipo_declaracao')
                ->where('empresa_id', $empresaId)
                ->selectRaw('COUNT(*) as total')
                ->groupBy('nacionalidade_transporte', 'tipo_declaracao')
                ->get();
            $pesoBrutoMedio = Licenciamento::select('tipo_transporte')
                ->where('empresa_id', $empresaId)
                ->selectRaw('AVG(peso_bruto) as peso_medio')
                ->groupBy('tipo_transporte')
                ->get();
            $cifMedio = Licenciamento::select('forma_pagamento')
                ->where('empresa_id', $empresaId)
                ->selectRaw('AVG(cif) as cif_medio')
                ->groupBy('forma_pagamento')
                ->get();
            $licencasPorMes = Licenciamento::where('empresa_id', $empresaId)
                ->selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
                ->groupBy('mes')
                ->orderBy('mes')
                ->get();
            $exportacoesPorMes = Licenciamento::where('empresa_id', $empresaId)
                ->where('tipo_declaracao', 21)
                ->selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
                ->groupBy('mes')
                ->orderBy('mes')
                ->get();
            $statusFatura = Licenciamento::select('forma_pagamento', 'status_fatura')
                ->where('empresa_id', $empresaId)
                ->selectRaw('COUNT(*) as total')
                ->groupBy('forma_pagamento', 'status_fatura')
                ->get();
            $atrasoPorPaisOrigem = Licenciamento::where('empresa_id', $empresaId)
                ->where('status_fatura', 'pendente')
                ->select('pais_origem')
                ->selectRaw('COUNT(*) as total')
                ->groupBy('pais_origem')
                ->get();
            $tempoMedioPortoEntrada = Licenciamento::select('porto_entrada')
                ->where('empresa_id', $empresaId)
                ->selectRaw('AVG(DATEDIFF(created_at, data_entrada)) as tempo_medio')
                ->groupBy('porto_entrada')
                ->get();
            $pesoBrutoPortoEntrada = Licenciamento::select('porto_entrada')
                ->where('empresa_id', $empresaId)
                ->selectRaw('AVG(peso_bruto) as peso_medio, COUNT(*) as total')
                ->groupBy('porto_entrada')
                ->orderByDesc('total')
                ->get();

            return [
                'totalLicenciamentos' => $totalLicenciamentos,
                'importacaoCount' => $importacaoCount,
                'exportacaoCount' => $exportacaoCount,
                'distribuicaoTransporte' => $distribuicaoTransporte,
                'nacionalidadeTransporte' => $nacionalidadeTransporte,
                'somaStatus' => $somaStatus,
                'mediaPesoBruto' => $mediaPesoBruto,
                'mediaFobTotal' => $mediaFobTotal,
                'mediaFrete' => $mediaFrete,
                'mediaSeguro' => $mediaSeguro,
                'mediaCif' => $mediaCif,
                'varianciaPesoBruto' => $varianciaPesoBruto,
                'desvioPadraoPesoBruto' => $desvioPadraoPesoBruto,
                'mediaVolume' => $mediaVolume,
                'tempoMedioProcessamento' => $tempoMedioProcessamento,
                'distribuicaoPortoEntrada' => $distribuicaoPortoEntrada,
                'distribuicaoPortoOrigem' => $distribuicaoPortoOrigem,
                'licenciamentosFormaPagamento' => $licenciamentosFormaPagamento,
                'statusFaturaPercentual' => $statusFaturaPercentual,
                'licencasPorMes' => $licencasPorMes,
                'tempoMedioProcessamentos' => $tempoMedioProcessamentos,
                'pesoBrutoMedio' => $pesoBrutoMedio,
                'cifMedio' => $cifMedio,
                'exportacoesPorMes' => $exportacoesPorMes,
                'statusFatura' => $statusFatura,
                'atrasoPorPaisOrigem' => $atrasoPorPaisOrigem,
                'tempoMedioPortoEntrada' => $tempoMedioPortoEntrada,
                'pesoBrutoPortoEntrada' => $pesoBrutoPortoEntrada,
            ];
        });
    }

    public function processoStats(int $empresaId): array
    {
        return Cache::remember("dashboard:processos:{$empresaId}", now()->addMinutes(5), function () use ($empresaId) {
            $processos = Processo::where('empresa_id', $empresaId)
                ->whereYear('created_at', now()->year)
                ->get();

            $totalProcessos = $processos->count();
            $tipoProcessosCount = $processos->groupBy(fn ($processo) => $processo->tipoProcesso->descricao ?? 'Desconhecido')->map->count();
            $mediaValorTotal = $processos->avg('valor_total');
            $mediaPesoBruto = $processos->avg('peso_bruto');
            $mediaVolume = $processos->avg('volume');
            $varianciaValorTotal = $processos->map(fn ($item) => pow($item->valor_total - $mediaValorTotal, 2))->avg();
            $desvioPadraoValorTotal = sqrt($varianciaValorTotal);
            $somaEstado = $processos->groupBy('Estado')->map->sum('valor_total');
            $distribuicaoPaisOrigem = $processos->groupBy('pais_origem')->map->count();
            $distribuicaoPaisDestino = $processos->groupBy('pais_destino')->map->count();
            $varianciaPesoBruto = $processos->map(fn ($item) => pow($item->peso_bruto - $mediaPesoBruto, 2))->avg();
            $desvioPadraoPesoBruto = sqrt($varianciaPesoBruto);
            $tempoMedioProcessamento = $processos->map(function ($item) {
                $entrada = Carbon::parse($item->data_entrada);
                $criado = Carbon::parse($item->created_at);
                return $entrada->diffInDays($criado);
            })->avg();
            $distribuicaoCliente = $processos->groupBy(fn ($item) => $item->customer->CompanyName ?? 'Desconhecido')->map->count();
            $estadoPercentual = $processos->groupBy('Estado')->map->count()->map(fn ($count) => $totalProcessos > 0 ? ($count / $totalProcessos) * 100 : 0);

            return compact(
                'processos', 'totalProcessos', 'tipoProcessosCount', 'mediaValorTotal', 'mediaPesoBruto',
                'mediaVolume', 'varianciaValorTotal', 'desvioPadraoValorTotal', 'somaEstado',
                'distribuicaoPaisOrigem', 'distribuicaoPaisDestino', 'varianciaPesoBruto',
                'desvioPadraoPesoBruto', 'tempoMedioProcessamento', 'distribuicaoCliente', 'estadoPercentual'
            );
        });
    }

    public function facturaStats(int $empresaId, int $ano): array
    {
        return Cache::remember("dashboard:factura:{$empresaId}:{$ano}", now()->addMinutes(5), function () use ($empresaId, $ano) {
            $totalFaturamentos = SalesInvoice::with('salesdoctotal')
                ->where('empresa_id', $empresaId)
                ->whereYear('created_at', $ano)
                ->get()
                ->sum(fn ($invoice) => $invoice->salesdoctotal->gross_total ?? 0);
            $numeroFaturas = SalesInvoice::where('empresa_id', $empresaId)->whereYear('created_at', $ano)->get();
            $ticketMedio = $totalFaturamentos / max(count($numeroFaturas), 1);
            $faturamentoMes = SalesDocTotal::whereMonth('created_at', now()->month)->sum('gross_total');
            $faturacaoHoje = SalesDocTotal::whereDate('created_at', Carbon::today())->sum('gross_total');
            $mesAnterior = SalesDocTotal::whereMonth('created_at', now()->month - 1)->sum('gross_total');
            $percentCrescimento = $mesAnterior > 0 ? (($faturacaoHoje - $mesAnterior) / $mesAnterior) * 100 : 0;
            $faturamentoAnoAnterior = SalesDocTotal::whereYear('created_at', now()->month - 1)->sum('gross_total');
            $lucroLiquidoTotal = SalesInvoice::where('empresa_id', $empresaId)->whereYear('created_at', $ano)->get()->sum(fn ($invoice) => ($invoice->salesdoctotal->gross_total ?? 0) - ($invoice->total_costs ?? 0));
            $percentualCrescimentoLucro = $faturamentoAnoAnterior > 0 ? (($lucroLiquidoTotal - $faturamentoAnoAnterior) / $faturamentoAnoAnterior) * 100 : 0;
            $custosTotais = SalesInvoice::where('empresa_id', $empresaId)->get()->sum(fn ($invoice) => $invoice->total_costs ?? 0);
            $percentualCrescimentoCustos = $faturamentoAnoAnterior > 0 ? (($custosTotais - $faturamentoAnoAnterior) / $faturamentoAnoAnterior) * 100 : 0;
            $dailyRevenue = $this->dailyRevenue(1);
            $previousDayRevenue = $this->previousDayRevenue();
            $sevenpreviousDaysRevenue = $this->dailyRevenue(7);
            $monthlyRevenue = $this->monthlyRevenue();
            $previousMonthRevenue = $this->previousMonthRevenue();
            $yearlyRevenue = $this->yearlyRevenue();
            $previousYearRevenue = $this->previousYearRevenue(Carbon::now()->subYear()->year);
            $previousYearRevenue2 = $this->previousYearRevenue(Carbon::now()->subYears(2)->year);
            $previousYearRevenue3 = $this->previousYearRevenue(Carbon::now()->subYears(3)->year);
            $percentualCrescimentoAnual = ($previousYearRevenue->first()->total ?? 0) > 0
                ? ((($yearlyRevenue->first()->total ?? 0) - ($previousYearRevenue->first()->total ?? 0)) / $previousYearRevenue->first()->total) * 100
                : 0;
            $faturamentoPorCliente = SalesInvoice::where('sales_invoice.empresa_id', $empresaId)
                ->join('customers', 'sales_invoice.customer_id', '=', 'customers.id')
                ->join('sales_document_totals', 'sales_invoice.id', '=', 'sales_document_totals.documentoID')
                ->select('customers.CompanyName', DB::raw('SUM(sales_document_totals.gross_total) as total'))
                ->groupBy('customers.CompanyName')
                ->orderByDesc('total')
                ->limit(5)
                ->get();
            $faturamentoPorProduto = SalesInvoice::where('sales_invoice.empresa_id', $empresaId)
                ->join('sales_line', 'sales_invoice.id', '=', 'sales_line.documentoID')
                ->join('produtos', 'sales_line.productID', '=', 'produtos.id')
                ->join('sales_document_totals', 'sales_invoice.id', '=', 'sales_document_totals.documentoID')
                ->select('produtos.ProductDescription', DB::raw('SUM(sales_line.credit_amount) as total'))
                ->groupBy('produtos.ProductDescription')
                ->orderByDesc('total')
                ->limit(5)
                ->get();
            $faturamentoUltimos3Meses = SalesDocTotal::where('created_at', '>=', Carbon::now()->subMonths(3))->sum('gross_total');
            $previsaoProximoMes = $faturamentoUltimos3Meses / 3;
            $faturamentoUltimos3Anos = SalesDocTotal::where('created_at', '>=', Carbon::now()->subYears(3))->sum('gross_total');
            $previsaoProximoAno = $faturamentoUltimos3Anos / 3;
            $tempos = SalesInvoice::where('empresa_id', $empresaId)
                ->orderBy('customer_id')->orderBy('created_at')
                ->get()
                ->groupBy('customer_id')
                ->map(function ($compras) {
                    $datas = $compras->pluck('created_at')->map(fn ($d) => Carbon::parse($d));
                    if ($datas->count() < 2) {
                        return null;
                    }
                    $intervalos = [];
                    for ($i = 1; $i < $datas->count(); $i++) {
                        $intervalos[] = $datas[$i]->diffInDays($datas[$i - 1]);
                    }
                    return count($intervalos) ? array_sum($intervalos) / count($intervalos) : null;
                })->filter()->avg();
            $faturacaoMensal = SalesDocTotal::selectRaw('MONTH(created_at) as mes, SUM(gross_total) as total')
                ->whereYear('created_at', $ano)
                ->groupBy('mes')
                ->orderBy('mes')
                ->get()
                ->pluck('total', 'mes')
                ->all();
            $faturacaoMensalCompleto = [];
            for ($i = 1; $i <= 12; $i++) {
                $faturacaoMensalCompleto[] = $faturacaoMensal[$i] ?? 0;
            }
            $anoAtual = Carbon::now()->year;
            $anosDisponiveis = range($anoAtual, $anoAtual - 3);
            $dadosPorAno = [];
            foreach ($anosDisponiveis as $anoItem) {
                $faturacaoBruta = SalesDocTotal::selectRaw('MONTH(created_at) as mes, SUM(gross_total) as total')
                    ->whereYear('created_at', $anoItem)
                    ->groupBy('mes')
                    ->orderBy('mes')
                    ->get()
                    ->pluck('total', 'mes')
                    ->all();
                $faturacaoMensalAno = [];
                for ($mes = 1; $mes <= 12; $mes++) {
                    $faturacaoMensalAno[] = $faturacaoBruta[$mes] ?? 0;
                }
                $dadosPorAno[$anoItem] = $faturacaoMensalAno;
            }

            return compact(
                'totalFaturamentos', 'numeroFaturas', 'ticketMedio', 'faturamentoMes', 'faturacaoHoje', 'mesAnterior',
                'percentCrescimento', 'faturamentoAnoAnterior', 'anosDisponiveis', 'dadosPorAno', 'lucroLiquidoTotal',
                'custosTotais', 'dailyRevenue', 'previousDayRevenue', 'sevenpreviousDaysRevenue', 'monthlyRevenue',
                'previousMonthRevenue', 'faturacaoMensalCompleto', 'yearlyRevenue', 'previousYearRevenue',
                'previousYearRevenue2', 'previousYearRevenue3', 'percentualCrescimentoAnual',
                'percentualCrescimentoLucro', 'percentualCrescimentoCustos', 'faturamentoPorCliente',
                'faturamentoPorProduto', 'previsaoProximoMes', 'previsaoProximoAno', 'tempos'
            );
        });
    }

    private function dailyRevenue(int $days)
    {
        return SalesDocTotal::selectRaw('DATE(created_at) as date, SUM(gross_total) as total')
            ->where('created_at', '>=', Carbon::today()->subDays($days - 1))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    private function previousDayRevenue()
    {
        return SalesDocTotal::selectRaw('DATE(created_at) as date, SUM(gross_total) as total')
            ->whereDate('created_at', Carbon::yesterday()->toDateString())
            ->groupBy('date')
            ->get();
    }

    private function monthlyRevenue()
    {
        return SalesDocTotal::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(gross_total) as total')
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('month', 'year')
            ->get();
    }

    private function previousMonthRevenue()
    {
        $previousMonth = Carbon::now()->subMonth();

        return SalesDocTotal::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(gross_total) as total')
            ->whereYear('created_at', $previousMonth->year)
            ->whereMonth('created_at', $previousMonth->month)
            ->groupBy('month', 'year')
            ->get();
    }

    private function yearlyRevenue()
    {
        return SalesDocTotal::selectRaw('YEAR(created_at) as year, SUM(gross_total) as total')
            ->groupBy('year')
            ->get();
    }

    private function previousYearRevenue(int $year)
    {
        return SalesDocTotal::selectRaw('YEAR(created_at) as year, SUM(gross_total) as total')
            ->whereYear('created_at', $year)
            ->groupBy('year')
            ->get();
    }
}
