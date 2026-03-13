<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SalesInvoice;
use App\Services\JasperService;
use App\Services\LicenciamentoReportService;
use App\Services\ReportService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class RelatorioController extends AuthenticatedController
{
    protected $jasper;

    public function __construct(
        JasperService $jasper,
        private readonly ReportService $reportService,
        private readonly LicenciamentoReportService $licenciamentoReportService
    )
    {
        parent::__construct();
        $this->jasper = $jasper;
    }

    public function generateReport($ProcessoID)
    {
        $file = $this->reportService->renderProcessReport($ProcessoID, Auth::id());

        if (!file_exists($file)) {
            abort(404);
        }

        return response()->file($file);
    }
    
    public function generateInvoices($invoiceNo){

        $invoices = SalesInvoice::findOrFail($invoiceNo);
        $file = $this->reportService->renderInvoiceReport($invoices, Auth::user()->name);

        if (!file_exists($file)) {
            abort(404);
        }

        return response()->file($file);

    }

    public function SelecionarRelatorio(Request $request)
    {
        return view('relatorios.selecionar');
    }

    public function RelatorioLicenciamento(Request $request)
    {
        $tipo = $request->input('tipo', 'cliente'); // Valor default para 'cliente'

        // Captura as datas de início e fim do intervalo a partir do request
        $dataInicio = $request->input('data_inicio', now()->startOfYear()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', now()->endOfYear()->format('Y-m-d'));

        if ($tipo === 'periodo') {
            $dataInicio = \Carbon\Carbon::createFromFormat('Y-m-d', $dataInicio)->format('Y-m-d');
            $dataFim = \Carbon\Carbon::createFromFormat('Y-m-d', $dataFim)->format('Y-m-d');
        }

        $relatorio = $this->licenciamentoReportService->forType(
            $tipo,
            $this->empresa->id,
            $dataInicio,
            $dataFim
        );

        if ($relatorio === null) {
            return response()->json(['erro' => 'Relatório não encontrado'], 404);
        }

        $jaspers = [
            'cliente' => resource_path('reports/relatorio_cliente.jrxml'),
            'tipo' => resource_path('reports/relatorio_tipo.jrxml'),
            'periodo' => resource_path('reports/relatorio_periodo.jrxml'),
            'localidade' => resource_path('reports/relatorio_localidade.jrxml'),
        ];
    
        $input = $jaspers[$tipo] ?? abort(404, 'Relatório não encontrado');
        $output = storage_path("app/reports/relatorio_$tipo");

        return view('relatorios.licenciamento', compact('relatorio', 'tipo'));
    }


}
