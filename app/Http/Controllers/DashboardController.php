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
    public function dailyRevenue($days)
    {
        return SalesDoctotal::selectRaw('DATE(created_at) as date, SUM(gross_total) as total')
            ->where('created_at', '>=', Carbon::today()->subDays($days - 1))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    // Faturamento do dia anterior
    public function previousDayRevenue()
    {
        $yesterday = Carbon::yesterday()->toDateString();
        return SalesDoctotal::selectRaw('DATE(created_at) as date, SUM(gross_total) as total')
            ->whereDate('created_at', $yesterday)
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

    // Faturamento do mês anterior
    public function previousMonthRevenue()
    {
        $previousMonth = Carbon::now()->subMonth();
        return SalesDoctotal::selectRaw('MONTH(created_at) as month, YEAR(created_at) as year, SUM(gross_total) as total')
            ->whereYear('created_at', $previousMonth->year)
            ->whereMonth('created_at', $previousMonth->month)
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

    // Faturamento do ano anterior ou de um ano específico
    public function previousYearRevenue($year)
    {
        return SalesDoctotal::selectRaw('YEAR(created_at) as year, SUM(gross_total) as total')
            ->whereYear('created_at', $year)
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
        $dailyRevenue = $this->dailyRevenue(1);
        $monthlyRevenue = $this->monthlyRevenue();
        $yearlyRevenue = $this->yearlyRevenue();
        $previousYearRevenue = $this->previousYearRevenue(Carbon::now()->subYear(1)->year); // ano anterior

        // Total de faturamento
        // $totalFaturamento = SalesDoctotal::sum('gross_total');
    
        // Faturamento do Mês Atual
        // $faturamentoMes = SalesDocTotal::whereMonth('created_at', now()->month)->sum('gross_total');

        // Faturamento de Hoje
        $faturacaoHoje = SalesDoctotal::whereDate('created_at', Carbon::today())->sum('gross_total');
        // Faturamento do Mês Anterior
        $mesAnterior = SalesDoctotal::whereMonth('created_at', now()->month - 1)->sum('gross_total');
        
        // Faturamento do Ano Anterior
        // $faturamentoAnoAnterior = SalesDoctotal::whereYear('created_at', now()->month - 1)->sum('gross_total');
        
        // Número de Faturas Emitidas
        $numeroFaturas = SalesInvoice::where('empresa_id', auth()->user()->empresas->first()->id)->get();

        // Calcule no controller e envie para a view:
        $ticketMedio = $totalFaturamento / max(count($numeroFaturas), 1);

        // Calcule no controller e envie para a view:
        $percentCrescimento = $mesAnterior > 0 ? (($faturacaoHoje - $mesAnterior) / $mesAnterior) * 100 : 0;

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
        'processesByCustomer', 'processesByCountries',
        'totalFaturamento', 
        'numeroFaturas', 'dailyRevenue', 
        'monthlyRevenue', 'yearlyRevenue', 
        'previousYearRevenue', 'processosPorEstado', 
        'faturacaoHoje', 'mesAnterior',
        'ticketMedio', 'percentCrescimento') );
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
        $processos = Processo::where('empresa_id', Auth::user()->empresas->first()->id)
        ->whereYear('created_at', now()->year)
        ->get();

        $totalProcessos = $processos->count();

        // Distribuição por tipo de processo (Count)
        $tipoProcessosCount = $processos->groupBy(function($processo) {
            return $processo->tipoProcesso->descricao ?? 'Desconhecido';
        })->map->count();
        
        // Cálculo das médias
        $mediaValorTotal = $processos->avg('valor_total');
        $mediaPesoBruto = $processos->avg('peso_bruto');
        $mediaVolume = $processos->avg('volume');

        // Variância e desvio padrão
        $varianciaValorTotal = $processos->map(fn($item) => pow($item->valor_total - $mediaValorTotal, 2))->avg();
        $desvioPadraoValorTotal = sqrt($varianciaValorTotal);

        // Soma dos valores por estado do processo
        $somaEstado = $processos->groupBy('Estado')->map->sum('valor_total');

        // Distribuição por país de origem e destino
        $distribuicaoPaisOrigem = $processos->groupBy('pais_origem')->map->count();
        $distribuicaoPaisDestino = $processos->groupBy('pais_destino')->map->count();

        // Estatísticas de peso e volume
        $mediaPesoBruto = $processos->avg('peso_bruto');
        $varianciaPesoBruto = $processos->map(fn($item) => pow($item->peso_bruto - $mediaPesoBruto, 2))->avg();
        $desvioPadraoPesoBruto = sqrt($varianciaPesoBruto);
        $mediaVolume = $processos->avg('volume');

        // Tempo de processamento (simulação: diferença entre data_entrada e created_at)
        $tempoMedioProcessamento = $processos->map(function($item) {
            $entrada = Carbon::parse($item->data_entrada);
            $criado = Carbon::parse($item->created_at);
            return $entrada->diffInDays($criado);
        })->avg();

        // Distribuição por cliente
        $distribuicaoCliente = $processos->groupBy(
            fn($item) => $item->customer->CompanyName ?? 'Desconhecido'
        )->map->count();

        // Percentual por estado do processo
        $estadoPercentual = $processos->groupBy('Estado')->map->count()->map(function ($count) use ($totalProcessos) {
            return ($count / $totalProcessos) * 100;
        });

    }

    // Estatísticas de Faturação - Controller
    public function FacturaEstatisticas(){

        // Total de faturamento
        $ano = request()->input('ano', Carbon::now()->year); // Pega o ano do request ou o ano atual
        $empresaId = auth()->user()->empresas->first()->id; // Pega o ID da empresa do usuário autenticado
        
        $totalFaturamentos = SalesInvoice::with('salesdoctotal')
            ->where('empresa_id', $empresaId)
            ->whereYear('created_at', $ano)
            ->get()
            ->sum(function ($invoice) {
                return $invoice->salesdoctotal->gross_total ?? 0;
            });
        
        // return view('dashboard_factura');
        // Número de Faturas Emitidas
        $numeroFaturas = SalesInvoice::where('empresa_id', $empresaId)->whereYear('created_at', $ano)->get();
        // Calcule no controller e envie para a view:
        $ticketMedio = $totalFaturamentos / max(count($numeroFaturas), 1);
        // Faturamento do Mês Atual
        $faturamentoMes = SalesDocTotal::whereMonth('created_at', now()->month)->sum('gross_total');
        // Faturamento de Hoje
        $faturacaoHoje = SalesDoctotal::whereDate('created_at', Carbon::today())->sum('gross_total');
        // Faturamento do Mês Anterior
        $mesAnterior = SalesDoctotal::whereMonth('created_at', now()->month - 1)->sum('gross_total');
        // Calcule no controller e envie para a view:
        $percentCrescimento = $mesAnterior > 0 ? (($faturacaoHoje - $mesAnterior) / $mesAnterior) * 100 : 0;
        // Faturamento do Ano Anterior
        $faturamentoAnoAnterior = SalesDoctotal::whereYear('created_at', now()->month - 1)->sum('gross_total');
        // Número de Transações Processadas (exemplo de como pode ser feito, depende da estrutura do seu modelo)
        // $numeroTransacoes = SalesInvoice::sum('total_transactions'); // Exemplo de campo "total_transactions"
        
        // Percentual de Faturas Pagas vs. Pendentes
        // $faturasPagas = SalesInvoice::where('status', 'paid')->count();
        // $faturasPendentes = SalesInvoice::where('status', 'pending')->count();
        // $totalFaturas = $faturasPagas + $faturasPendentes;
        // $percentualPagas = $totalFaturas > 0 ? ($faturasPagas / $totalFaturas) * 100 : 0;
        // $percentualPendentes = $totalFaturas > 0 ? ($faturasPendentes / $totalFaturas) * 100 : 0;
        
        //Lucro Líquido Total
        $lucroLiquidoTotal = SalesInvoice::where('empresa_id', $empresaId)->whereYear('created_at', $ano)
        ->get()
        ->sum(function ($invoice) {
            return ($invoice->salesdoctotal->gross_total ?? 0) - ($invoice->total_costs ?? 0);
        });

        $percentualCrescimentoLucro = $faturamentoAnoAnterior > 0 ? (($lucroLiquidoTotal - $faturamentoAnoAnterior) / $faturamentoAnoAnterior) * 100 : 0;

        //Custos Totais
        $custosTotais = SalesInvoice::where('empresa_id', $empresaId)
        ->get()
        ->sum(function ($invoice) {
            return $invoice->total_costs ?? 0;
        });

        $percentualCrescimentoCustos = $faturamentoAnoAnterior > 0 ? (($custosTotais - $faturamentoAnoAnterior) / $faturamentoAnoAnterior) * 100 : 0;

        // Margem de Lucro Média
        // $margemLucroMedia = $totalFaturamentos > 0 ? ($lucroLiquidoTotal / $totalFaturamentos) * 100 : 0;  

        // Facturamento por Categoria de Produto
        // $facturamentoPorCategoria = SalesInvoice::where('empresa_id', auth()->user()->empresas->first()->id)
        // ->join('sales_items', 'sales_invoices.id', '=', 'sales_items.sales_invoice_id')
        // ->join('products', 'sales_items.product_id', '=', 'products.id')
        // ->select('products.category', DB::raw('SUM(sales_items.total) as total'))
        // ->groupBy('products.category')
        // ->get();

        // Dados para gráficos de faturamento diário, mensal e anual  
        $dailyRevenue = $this->dailyRevenue(1); // dia
        $previousDayRevenue = $this->previousDayRevenue(); // dia anterior
        $sevenpreviousDaysRevenue = $this->dailyRevenue(7); // 7 dias atrás

        $monthlyRevenue = $this->monthlyRevenue(); // mês
        $previousMonthRevenue = $this->previousMonthRevenue(); // mês anterior

        // Calcular faturamento anual e dos anos anteriores
        $yearlyRevenue = $this->yearlyRevenue(); // ano atual
        $previousYearRevenue = $this->previousYearRevenue(Carbon::now()->subYear(1)->year); // ano anterior
        $previousYearRevenue2 = $this->previousYearRevenue(Carbon::now()->subYears(2)->year); // 2 anos atrás
        $previousYearRevenue3 = $this->previousYearRevenue(Carbon::now()->subYears(3)->year); // 3 anos atrás

        // Percentual de crescimento anual
        $percentualCrescimentoAnual = $previousYearRevenue->first()->total > 0
            ? (($yearlyRevenue->first()->total - $previousYearRevenue->first()->total) / $previousYearRevenue->first()->total) * 100
            : 0;

        // Dados para gráficos de faturamento por cliente
        $faturamentoPorCliente = SalesInvoice::where('sales_invoice.empresa_id', auth()->user()->empresas->first()->id)
        ->join('customers', 'sales_invoice.customer_id', '=', 'customers.id')
        ->join('sales_document_totals', 'sales_invoice.id', '=', 'sales_document_totals.documentoID')
        ->select('customers.CompanyName', DB::raw('SUM(sales_document_totals.gross_total) as total'))
        ->groupBy('customers.CompanyName')
        ->orderByDesc('total')
        ->limit(5)
        ->get();

        // Dados para gráficos de faturamento por produto
        $faturamentoPorProduto = SalesInvoice::where('sales_invoice.empresa_id', auth()->user()->empresas->first()->id)
        ->join('sales_line', 'sales_invoice.id', '=', 'sales_line.documentoID')
        ->join('produtos', 'sales_line.productID', '=', 'produtos.id')
        ->join('sales_document_totals', 'sales_invoice.id', '=', 'sales_document_totals.documentoID')
        ->select('produtos.ProductDescription', DB::raw('SUM(sales_line.credit_amount) as total'))
        ->groupBy('produtos.ProductDescription')
        ->orderByDesc('total')
        ->limit(5)
        ->get();

        // Previsão de faturamento para o próximo mês (simples média dos últimos 3 meses)
        $faturamentoUltimos3Meses = SalesDoctotal::where('created_at', '>=', Carbon::now()->subMonths(3))
        ->sum('gross_total');
        $previsaoProximoMes = $faturamentoUltimos3Meses / 3;

        // Previsão de faturamento para o próximo ano (simples média dos últimos 3 anos)
        $faturamentoUltimos3Anos = SalesDoctotal::where('created_at', '>=', Carbon::now()->subYears(3))
        ->sum('gross_total');
        $previsaoProximoAno = $faturamentoUltimos3Anos / 3;

        // Tempo Médio Entre Compras por cliente
        $tempos = SalesInvoice::where('empresa_id', auth()->user()->empresas->first()->id)
            ->orderBy('customer_id')->orderBy('created_at')
            ->get()
            ->groupBy('customer_id')
            ->map(function($compras) {
                $datas = $compras->pluck('created_at')->map(fn($d) => Carbon::parse($d));
                if ($datas->count() < 2) return null;
                $intervalos = [];
                for ($i = 1; $i < $datas->count(); $i++) {
                    $intervalos[] = $datas[$i]->diffInDays($datas[$i-1]);
                }
                return count($intervalos) ? array_sum($intervalos) / count($intervalos) : null;
            })->filter()->avg();
// 
        $faturacaoMensal = SalesDocTotal::selectRaw('MONTH(created_at) as mes, SUM(gross_total) as total')
            ->whereYear('created_at', $ano)
            ->groupBy('mes')
            ->orderBy('mes')
            ->get()
            ->pluck('total', 'mes')
            ->all();

        // Garante que todos os meses estejam presentes no array (meses sem vendas ficam com 0)
        $faturacaoMensalCompleto = [];
        for ($i = 1; $i <= 12; $i++) {
            $faturacaoMensalCompleto[] = isset($faturacaoMensal[$i]) ? $faturacaoMensal[$i] : 0;
        }

        // Definir o intervalo de anos (ex: últimos 4 anos incluindo o atual)
        $anoAtual = Carbon::now()->year;
        $anosDisponiveis = range($anoAtual, $anoAtual - 3); // retorna [2025, 2024, 2023, 2022]

        // Inicializa array para armazenar os dados completos de faturação por ano
        $dadosPorAno = [];

        foreach ($anosDisponiveis as $ano) {
            // Buscar total de faturação por mês para o ano atual
            $faturacaoBruta = SalesDocTotal::selectRaw('MONTH(created_at) as mes, SUM(gross_total) as total')
                ->whereYear('created_at', $ano)
                ->groupBy('mes')
                ->orderBy('mes')
                ->get()
                ->pluck('total', 'mes')
                ->all();

            // Garantir que todos os 12 meses estejam presentes com valores (0 se ausente)
            $faturacaoMensal = [];
            for ($mes = 1; $mes <= 12; $mes++) {
                $faturacaoMensal[] = $faturacaoBruta[$mes] ?? 0;
            }

            // Armazena os dados completos do ano
            $dadosPorAno[$ano] = $faturacaoMensal;
        }
        // Enviar todos os dados para a view
        return view('dashboard_factura', compact(
            'totalFaturamentos', 'numeroFaturas', 'ticketMedio',
            'faturamentoMes', 'faturacaoHoje', 'mesAnterior', 'percentCrescimento',
            'faturamentoAnoAnterior', 'anosDisponiveis', 'dadosPorAno',
            // 'numeroTransacoes', 
            // 'faturasPagas', 'faturasPendentes', 'percentualPagas', 'percentualPendentes',
            'lucroLiquidoTotal', 'custosTotais', // 'margemLucroMedia', 'facturamentoPorCategoria',
            'dailyRevenue', 'previousDayRevenue', 'sevenpreviousDaysRevenue',
            'monthlyRevenue', 'previousMonthRevenue', 'faturacaoMensalCompleto',
            'yearlyRevenue', 'previousYearRevenue', 'previousYearRevenue2', 'previousYearRevenue3',
            'percentualCrescimentoAnual', 'percentualCrescimentoLucro', 'percentualCrescimentoCustos',
            'faturamentoPorCliente', 'faturamentoPorProduto',
            'previsaoProximoMes',  'previsaoProximoAno'
        ));
    }


}
