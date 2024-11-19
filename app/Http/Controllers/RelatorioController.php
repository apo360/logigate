<?php

namespace App\Http\Controllers;

use App\Models\Licenciamento;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Processo;
use App\Models\SalesInvoice;
use App\Models\User;
use App\Services\JasperService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use PHPJasper\PHPJasper;

class RelatorioController extends Controller
{
    protected $jasper;

    public function __construct(JasperService $jasper)
    {
        $this->jasper = $jasper;
    }

    public function generateReport($ProcessoID)
    {

        //auth()->user()->empresa->Empresa;

        $input = base_path('reports/hello_world.jrxml'); // Certifique-se de que este arquivo existe
        $output = base_path('reports');

        // Configuração da conexão com o banco de dados
        $connection = [
            'driver' => 'mysql', // Substitua pelo driver apropriado
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
        ];

        $options = [
            'format' => ['pdf'],
            'locale' => 'en',
            'params' => [
                'P_processo' => $ProcessoID,
                'P_user' => Auth::user()->id,
            ],
            'db_connection' => $connection,
        ];


        $jasper = new PHPJasper();

        $jasper->process(
            $input,
            $output,
            $options
        )->execute();

        $file = $output . '/hello_world.pdf';

        if (!file_exists($file)) {
            abort(404);
        }

        return response()->file($file);
    }
    
    public function generateInvoices($invoiceNo){

        $invoices = SalesInvoice::findOrFail($invoiceNo);

        // Caminho completo para o template .jasper
        $input = base_path('reports/facturas_geral.jrxml'); // Certifique-se de que este arquivo existe
        $output = base_path('reports');
    
        // Definir os parâmetros
        $options = [
            'format' => ['pdf'],
            'locale' => 'en',
            'params' => [
                // Dados Empresa
                'despachante' => $invoices->empresa->Empresa,
                'nif' => $invoices->empresa->NIF,
                'cedula' => $invoices->empresa->Cedula,
                'telefone' => $invoices->empresa->Contacto_fixo,
                'logotipo' => public_path($invoices->empresa->Logotipo),
                'endereco' => $invoices->empresa->Endereco_completo,
                'operador' => Auth::user()->name,

                // Dados do cliente
                'cliente' => $invoices->customer->CompanyName,
                'nif_cliente' => $invoices->customer->CustomerTaxID,
                'morada_cliente' => $invoices->customer->CompanyName ?? 'Desconhecido',

                // Dados Facturas
                'invoice_no' => $invoices->invoiceType->Descriptions. ' ' .$invoices->invoice_no,
                'data_emissao' => $invoices->invoiceType->invoice_date ? $invoices->invoiceType->invoice_date->format('d-m-Y') : '01-01-2024',
                'data_vencimento' => $invoices->invoiceType->invoice_date_end ? $invoices->invoiceType->invoice_date_end->format('d-m-Y') : '31-01-2024',
                 // Converter para string se o template espera string
                
            ],
        ];

        $jasper = new PHPJasper();

        $jasper->process(
            $input,
            $output,
            $options
        )->execute();

        $file = $output . '/facturas_geral.pdf';

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

        switch ($tipo) {
            case 'cliente':
                $relatorio = Licenciamento::select([
                    'customers.CompanyName AS Cliente',
                    'licenciamentos.tipo_declaracao AS Tipo_de_Licenciamento',
                    DB::raw('MAX(licenciamentos.created_at) AS `Última Emissão`'),
                    DB::raw('COUNT(licenciamentos.id) AS Total')
                ])
                ->leftJoin('customers', 'licenciamentos.cliente_id', '=', 'customers.id')
                ->where('licenciamentos.empresa_id', Auth::user()->empresas->first()->id)
                ->groupBy('customers.CompanyName', 'licenciamentos.tipo_declaracao')
                ->orderBy('customers.CompanyName')
                ->get();
                break;
            
            case 'tipo':
                $relatorio = Licenciamento::select([
                    'tipo_declaracao AS Tipo_de_Licenciamento',
                    DB::raw('COUNT(*) AS Quantidade_Total'),
                    DB::raw('ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM licenciamentos WHERE empresa_id = ?)), 2) AS Percentual', [Auth::user()->empresas->first()->id])
                ])
                ->where('licenciamentos.empresa_id', Auth::user()->empresas->first()->id)
                ->groupBy('tipo_declaracao')
                ->orderByDesc('Quantidade_Total')
                ->get();
                break;
            
            case 'periodo':

                // Validação de formato de data
                $dataInicio = \Carbon\Carbon::createFromFormat('Y-m-d', $dataInicio)->format('Y-m-d');
                $dataFim = \Carbon\Carbon::createFromFormat('Y-m-d', $dataFim)->format('Y-m-d');

                // Consulta Eloquent
                $relatorio = Licenciamento::select([
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m') AS Mes_Ano"),
                    'tipo_declaracao AS Tipo_de_Licenciamento',
                    DB::raw('COUNT(*) AS Total_Licenciamentos')
                ])
                ->where('licenciamentos.empresa_id', Auth::user()->empresas->first()->id)
                ->whereBetween('created_at', [$dataInicio, $dataFim])
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"), 'tipo_declaracao')
                ->orderBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
                ->orderBy('tipo_declaracao')
                ->get();
                break;
            
            case 'localidade':
                $relatorio = Licenciamento::select([
                    DB::raw("CONCAT(pais_origem, IF(porto_origem IS NOT NULL, CONCAT(' - ', porto_origem), '')) AS Localidade"),
                    DB::raw("CASE 
                        WHEN tipo_declaracao = 11 THEN 'Importação'
                        WHEN tipo_declaracao = 21 THEN 'Exportação'
                        ELSE 'Outro'
                    END AS Tipo_de_Licenciamento"),
                    DB::raw('COUNT(*) AS Quantidade'),
                    DB::raw('ROUND((COUNT(*) * 100) / SUM(COUNT(*)) OVER (), 2) AS Percentual')
                ])
                ->where('licenciamentos.empresa_id', Auth::user()->empresas->first()->id)
                ->groupBy('pais_origem', 'porto_origem', 'tipo_declaracao')
                ->orderBy('pais_origem')
                ->orderBy('porto_origem')
                ->orderBy('tipo_declaracao')
                ->get();
                break;
            
            default:
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
