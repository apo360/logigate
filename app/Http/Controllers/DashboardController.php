<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Exportador;
use App\Models\Processo;
use App\Models\Licenciamento;
use App\Models\SalesDocTotal;
use App\Models\SalesInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    
    // Faturamento diário
    public function dailyRevenue()
    {
        return SalesDoctotal::selectRaw('DATE(created_at) as date, SUM(gross_total) as total')
            ->whereDate('created_at', Carbon::today())
            ->groupBy('date')
            ->get();
    }

    // Faturamento mensal
    public function monthlyRevenue()
    {
        return SalesDoctotal::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(gross_total) as total')
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('month', 'year')
            ->get();
    }

    // Faturamento anual
    public function yearlyRevenue()
    {
        return SalesDoctotal::selectRaw('YEAR(created_at) as year, SUM(gross_total) as total')
            ->groupBy('year')
            ->get();
    }

    // Faturamento do ano anterior
    public function previousYearRevenue()
    {
        return SalesDoctotal::selectRaw('YEAR(created_at) as year, SUM(gross_total) as total')
            ->whereYear('created_at', Carbon::now()->subYear()->year)
            ->groupBy('year')
            ->get();
    }

    public function index(){

        $licenciamento = Licenciamento::where('empresa_id', Auth::user()->empresas->first()->id)->get();
        $processos = Processo::where('empresa_id', auth()->user()->empresas->first()->id)->get();
        $clientes = Customer::where('empresa_id', auth()->user()->empresas->first()->id)->get();
        $exportadores = Exportador::where('empresa_id', auth()->user()->empresas->first()->id)->get();
        $totalFaturamento = SalesInvoice::with('salesdoctotal')
        ->get()
        ->sum(function ($invoice) {
            return $invoice->salesdoctotal->gross_total ?? 0;
        });

        // Dados de faturamento diário, mensal e anual
        $dailyRevenue = $this->dailyRevenue();
        $monthlyRevenue = $this->monthlyRevenue();
        $yearlyRevenue = $this->yearlyRevenue();
        $previousYearRevenue = $this->previousYearRevenue();

        // Total de faturamento
        $totalFaturamento = SalesDoctotal::sum('gross_total');
    
        // Faturamento do Mês Atual
        // $faturamentoMes = SalesDocTotal::whereMonth('created_at', now()->month)->sum('gross_total');
        
        // Faturamento do Ano Anterior
        // $faturamentoAnoAnterior = SalesDoctotal::whereYear('created_at', now()->month - 1)->sum('gross_total');
        
        // Número de Faturas Emitidas
        $numeroFaturas = SalesInvoice::where('empresa_id', auth()->user()->empresas->first()->id)->get();

        // Número de Transações Processadas (exemplo de como pode ser feito, depende da estrutura do seu modelo)
        // $numeroTransacoes = SalesInvoice::sum('total_transactions'); // Exemplo de campo "total_transactions"


        $processesByCountries = Processo::where('empresa_id', Auth::user()->empresas->first()->id)
            ->join('importacao', 'importacao.processo_id', '=', 'processos.id')
            ->join('paises', 'importacao.Fk_pais_origem', '=', 'paises.id')
            ->select('paises.pais as paisss', DB::raw('count(processos.id) as total'))
            ->groupBy('paises.pais')
            ->orderByDesc('total')
            ->limit(7)
            ->get();
            
        $topCountries = Processo::where('empresa_id', Auth::user()->empresas->first()->id)
            ->join('importacao', 'processos.id', '=', 'importacao.processo_id')
            ->join('paises', 'importacao.Fk_pais_origem', '=', 'paises.id')
            ->select('paises.pais', DB::raw('count(processos.id) as total'))
            ->groupBy('paises.pais')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        $processesByCustomer = Customer::where('customers.empresa_id', Auth::user()->empresas->first()->id)
            ->join('processos', 'processos.customer_id', '=', 'customers.id')
            ->select('customers.CompanyName', DB::raw('count(processos.id) as total'))
            ->groupBy('customers.CompanyName')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        // Exemplo: Buscar a quantidade de processos por estado
        $processosPorEstado = Processo::select('Estado', DB::raw('count(*) as total'))
        ->groupBy('Estado')
        ->orderByDesc('total')
        ->get();

        return view('dashboard', compact('clientes','exportadores',
        'processos','licenciamento','topCountries', 
        'processesByCustomer', 'processesByCountries', 'totalFaturamento',
        'totalFaturamento', 
        'numeroFaturas', 'dailyRevenue', 'monthlyRevenue', 'yearlyRevenue', 'previousYearRevenue', 'processosPorEstado'));
    }


    public function licenciamentoEstatisticas() {
        $licenciamentos = Licenciamento::where('empresa_id', Auth::user()->empresas->first()->id)->get();

        $totalLicenciamentos = $licenciamentos->count();
        $importacaoCount = $licenciamentos->where('tipo_declaracao', 11)->count();
        $exportacaoCount = $licenciamentos->where('tipo_declaracao', 21)->count();

        // Cálculo das médias
        $mediaPesoBruto = $licenciamentos->avg('peso_bruto');
        $mediaFobTotal = $licenciamentos->avg('fob_total');
        $mediaFrete = $licenciamentos->avg('frete');
        $mediaSeguro = $licenciamentos->avg('seguro');
        $mediaCif = $licenciamentos->avg('cif');

        // Variância e desvio padrão
        $varianciaPesoBruto = $licenciamentos->map(fn($item) => pow($item->peso_bruto - $mediaPesoBruto, 2))->avg();
        $desvioPadraoPesoBruto = sqrt($varianciaPesoBruto);

        // Soma dos valores por status da fatura
        $somaStatus = $licenciamentos->groupBy('status_fatura')->map->sum('fob_total');

        // Distribuição por tipo de transporte e nacionalidade do transporte
        $distribuicaoTransporte = $licenciamentos->groupBy('tipo_transporte')->map->count();
        $nacionalidadeTransporte = $licenciamentos->groupBy('nacionalidade_transporte')->map->count();

        // Estatísticas de peso e volume
        $mediaPesoBruto = $licenciamentos->avg('peso_bruto');
        $varianciaPesoBruto = $licenciamentos->map(fn($item) => pow($item->peso_bruto - $mediaPesoBruto, 2))->avg();
        $desvioPadraoPesoBruto = sqrt($varianciaPesoBruto);
        $mediaVolume = $licenciamentos->avg('qntd_volume');

        // Tempo de processamento (simulação: diferença entre data_entrada e created_at)
        $tempoMedioProcessamento = $licenciamentos->map(function($item) {
            $entrada = Carbon::parse($item->data_entrada);
            $criado = Carbon::parse($item->created_at);
            return $entrada->diffInDays($criado);
        })->avg();

        // Distribuição por porto de entrada e porto de origem
        $distribuicaoPortoEntrada = $licenciamentos->groupBy('porto_entrada')->map->count();
        $distribuicaoPortoOrigem = $licenciamentos->groupBy('porto_origem')->map->count();

        // Licenciamentos por forma de pagamento
        $licenciamentosFormaPagamento = $licenciamentos->groupBy('forma_pagamento')->map->count();

        // Percentual por status de fatura
        $statusFaturaPercentual = $licenciamentos->groupBy('status_fatura')->map->count()->map(function ($count) use ($totalLicenciamentos) {
            return ($count / $totalLicenciamentos) * 100;
        });

        // Volume de licenças ao longo do tempo (exemplo: agrupado por mês)
        $licencasPorMes = $licenciamentos->groupBy(function($item) {
            return Carbon::parse($item->created_at)->format('Y-m');
        })->map->count();

        // Hipótese 1: Tempo médio de processamento por tipo de declaração
        $tempoMedioProcessamentos = [
            'importacao' => Licenciamento::where('tipo_declaracao', 11)
                ->selectRaw('AVG(DATEDIFF(created_at, data_entrada)) as tempo_medio')
                ->first()->tempo_medio,
            'exportacao' => Licenciamento::where('tipo_declaracao', 21)
                ->selectRaw('AVG(DATEDIFF(created_at, data_entrada)) as tempo_medio')
                ->first()->tempo_medio,
        ];

        // Hipótese 2: Nacionalidade do transporte por tipo de declaração
        $nacionalidadeTransporte = Licenciamento::select('nacionalidade_transporte', 'tipo_declaracao')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('nacionalidade_transporte', 'tipo_declaracao')
            ->get();

        // Hipótese 3: Peso bruto médio por tipo de transporte
        $pesoBrutoMedio = Licenciamento::select('tipo_transporte')
            ->selectRaw('AVG(peso_bruto) as peso_medio')
            ->groupBy('tipo_transporte')
            ->get();

        // Hipótese 4: CIF médio por forma de pagamento
        $cifMedio = Licenciamento::select('forma_pagamento')
            ->selectRaw('AVG(cif) as cif_medio')
            ->groupBy('forma_pagamento')
            ->get();

        // Hipótese 5: Licenciamentos por mês
        $licencasPorMes = Licenciamento::selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // Hipótese 6: Exportações por mês
        $exportacoesPorMes = Licenciamento::where('tipo_declaracao', 21)
            ->selectRaw('MONTH(created_at) as mes, COUNT(*) as total')
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        // Hipótese 7: Status da fatura por forma de pagamento
        $statusFatura = Licenciamento::select('forma_pagamento', 'status_fatura')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('forma_pagamento', 'status_fatura')
            ->get();

        // Hipótese 8: Atraso no pagamento por país de origem
        $atrasoPorPaisOrigem = Licenciamento::where('status_fatura', 'pendente')
            ->select('pais_origem')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('pais_origem')
            ->get();

        // Hipótese 9: Tempo médio de processamento por porto de entrada
        $tempoMedioPortoEntrada = Licenciamento::select('porto_entrada')
            ->selectRaw('AVG(DATEDIFF(created_at, data_entrada)) as tempo_medio')
            ->groupBy('porto_entrada')
            ->get();

        // Hipótese 10: Peso bruto médio por porto de entrada
        $pesoBrutoPortoEntrada = Licenciamento::select('porto_entrada')
            ->selectRaw('AVG(peso_bruto) as peso_medio, COUNT(*) as total')
            ->groupBy('porto_entrada')
            ->orderByDesc('total')
            ->get();


        return view('dashboard_licenciamento', compact(
            'totalLicenciamentos', 'importacaoCount', 'exportacaoCount',
            'distribuicaoTransporte', 'nacionalidadeTransporte', 'somaStatus',
            'mediaPesoBruto', 'mediaFobTotal', 'mediaFrete', 'mediaSeguro', 'mediaCif',
            'varianciaPesoBruto', 'desvioPadraoPesoBruto', 'mediaVolume',
            'tempoMedioProcessamento', 'distribuicaoPortoEntrada', 'distribuicaoPortoOrigem',
            'licenciamentosFormaPagamento', 'statusFaturaPercentual', 'licencasPorMes',
            'tempoMedioProcessamentos',
                'nacionalidadeTransporte',
                'pesoBrutoMedio',
                'cifMedio',
                'licencasPorMes',
                'exportacoesPorMes',
                'statusFatura',
                'atrasoPorPaisOrigem',
                'tempoMedioPortoEntrada',
                'pesoBrutoPortoEntrada'
        ));

    }

    public function ProcessosEstatisticas(){

    }


}
