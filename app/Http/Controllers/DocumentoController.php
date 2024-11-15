<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Models\Customer;
use App\Models\Documento;
use App\Models\InvoiceType;
use App\Models\Licenciamento;
use App\Models\MetodoPagamento;
use App\Models\Processo;
use App\Models\ProcLicenFactura;
use App\Models\Produto;
use App\Models\SalesDocTotal;
use App\Models\SalesInvoice;
use App\Models\SalesLine;
use App\Models\SalesStatus;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DocumentoController extends Controller
{
    private function signAndSaveHash($invoiceId)
    {
        // Obtenha o modelo SalesInvoice
        $invoice = SalesInvoice::find($invoiceId);

        $DocTotal = SalesDocTotal::where('documentoID',$invoiceId)->first();

        // Campos a serem assinados
        $fieldsToSign = [
            $invoice->invoice_date, 
            $invoice->getSystemEntryDate(), 
            $invoice->invoice_no, 
            $DocTotal->gross_total
        ];

        // Crie a mensagem a ser assinada
        $messageToSign = implode(';', $fieldsToSign);

        // Salve a mensagem em um arquivo temporário
        $filePath = storage_path('app/temp_message.txt');
        file_put_contents($filePath, $messageToSign);

        // Caminho da chave privada
        $privateKeyPath = '/var/www/logi/ocean_system/sea/weave/fechadura_rest.pem';

        if (!file_exists($privateKeyPath)) {
            throw new Exception("Private key file not found.");
        }

        // Carregar a chave privada
        $privateKey = openssl_pkey_get_private(file_get_contents($privateKeyPath), null);
        if (!$privateKey) {
            throw new Exception("Failed to load private key.");
        }

        // Assine a mensagem com SHA-1 e PKCS1 v1.5 padding
        if (!openssl_sign($messageToSign, $signature, $privateKey, OPENSSL_ALGO_SHA1)) {
            throw new Exception("Failed to sign the message.");
        }

        // Codifique para base64
        $base64Signature = base64_encode($signature);

        // Atualize o modelo SalesInvoice com o hash assinado
        $invoice->hash = $base64Signature;

        if (!$invoice->save()) {
            throw new Exception("Failed to save invoice hash.");
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(){

        $invoices = SalesInvoice::all();

        $faturasPagas = SalesDocTotal::whereNotNull('data_pagamento')->count();
        $faturasPorPagar = SalesDocTotal::whereNull('data_pagamento')->count();
        $faturasEmAtraso = SalesDocTotal::whereNull('data_pagamento')->where('data_pagamento', '<', now())->count();

        $tableData = [
            'headers' => ['Tipo', 'Número da Fatura', 'Cliente', 'Total', 'Estado',''],
            'rows' => [],
        ];

        foreach ($invoices as $key => $fatura ) {
            $tableData['rows'][] = [
                '<div id="doc-header-type" data-href="#/office/change/">
                    <div style="background: gray; border-radius: 50px;" class="inline-flex items-center px-3 py-2 border ">
                    '.$fatura->invoiceType->Code.'
                    </div> 
                </div>',
                $fatura->invoice_no,
                $fatura->customer->CompanyName ?? '',
                $fatura->salesdoctotal->gross_total ?? '0.00',
                '', // ---> faturasPagas, faturasPorPagar, faturasEmAtraso
                '
                   <div class="inline-flex">
                    <a href="'.route('documentos.show', $fatura).'" class="btn btn-sm "><i class="fas fa-eye"></i></a>
                    <a href="'.route('documento.print', $fatura->id).'" class="btn btn-sm "><i class="fas fa-print"></i></a>
                   </div>         
                ',
            ];
        }

        return view('Documentos.index', compact('tableData','invoices','faturasPagas', 'faturasPorPagar', 'faturasEmAtraso'));
    }

    /**
     * Filtrar as pesquisas de documentos
     */

    public function filtrar(Request $request)
    {
        $dataInicial = $request->input('dataInicial');
        $dataFinal = $request->input('dataFinal');

        $faturas = SalesDocTotal::whereBetween('invoice_date', [$dataInicial, $dataFinal])->get();

        return view('Documentos.index', compact('faturas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $tipoDocumentos = DB::table('invoice_types')->get();
        
        // Verifica se o parâmetro 'id' está presente no request
        if ($request->has('licenciamento_id')) {
            $id = $request->input('licenciamento_id');
            $licenciamento = Licenciamento::Find($id);

            // 
            $produtos = Produto::where('ProductType', 'S')->where('ProductGroup', 1)->get();
            
            // Busca o processo ou dado relacionado ao id
            $processo = Processo::findOrFail($id); // Encontrar o processo pelo ID
            
            // Retorna a view associada quando o 'id' existe
            return view('Documentos.create_documento', compact('licenciamento', 'produtos', 'tipoDocumentos'));
        } else {
            
            $produtos = DB::table('Listar_Produtos')->get();

            // Retorna a view padrão de criação quando não há 'id'
            $clientes = Customer::where('empresa_id', Auth::user()->empresas->first()->id ?? null)->get();

            // Gera novo código para o cliente
            $newCustomerCode = Customer::generateNewCode();

            return view('Documentos.create_documento_2', compact('clientes', 'tipoDocumentos', 'produtos', 'newCustomerCode'));
        }
    }

    // Metodo para criar o documento com parametros
    public function createParams($licenciamento_id = null, $processo_id = null){

        // Inicializa variáveis para controle
        $licenciamento = null; $processo = null; $cliente = null;
        $produtos = Produto::where('ProductType', 'S')->where('ProductGroup', 1)->get();

        // Verifica se o licenciamento_id foi passado
        if ($licenciamento_id) 
        { 
            $licenciamento = Licenciamento::find($licenciamento_id);
            $detalhe = "Licenciamento cod." . $licenciamento->codigo_licenciamento;
            $cliente = $licenciamento->cliente;
        }

        // Verifica se o processo_id foi passado
        if ($processo_id) { 
            $processo = Processo::find($processo_id);
            $detalhe = "Processo cod." . $processo->id;
            $cliente = $processo->cliente;
        }
        
        return view('Documentos.create_documento', compact('produtos', '$cliente'));

    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Inicializar as variaveis
        $SumTax = $SumNetTotal = $SumGrossTotal = 0;

        $invoiceTypeID = (new InvoiceType())->getID($request->input('document_type'));

        try {

            // Executar o procedimento armazenado para obter o próximo número de fatura
            $result = DB::select("CALL GenerateInvoiceNo(?,?)", [$invoiceTypeID, Auth::user()->empresas->first()->id]);

            $salesInvoice = SalesInvoice::create([
                'invoice_no' => $result[0]->InvoiceNo,
                'hash' => '0',
                'hash_control' => '0',
                'period' => 1,
                'invoice_date' => $request->input('invoice_date'),
                'invoice_date_end' => $request->input('data_vencimento'),
                'self_billing_indicator' => 0,
                'cash_vat_scheme_indicator' => 0,
                'third_parties_billing_indicator' => 0,
                'invoice_type_id' => $invoiceTypeID,
                'source_id' => Auth::user()->id,
                'system_entry_date' => Carbon::now()->toDateTimeString(), // ou 'nullable|date_format:Y-m-d H:i:s',
                'customer_id' => $request->input('customer_id'), 
            ]);

           // Verificar se existe licenciamento ou processo no request
            if (!empty($request->input('licenciamento_id'))) {
                
                Licenciamento::where('id', $request->input('licenciamento_id'))->update([
                    'Nr_factura' => $salesInvoice->invoice_no,
                    'status_fatura' => 'emitida'
                ]);

                ProcLicenFactura::create([
                    'empresa_id' => Auth::user()->empresas->first()->id,
                    'licenciamento_id' => $request->input('licenciamento_id'),
                    'processo_id' => null,
                    'fatura_id' => $salesInvoice->id,
                    'status_fatura' => 'emitida'
                ]);
            } elseif (!empty($request->input('processo_id'))) {
                ProcLicenFactura::create([
                    'empresa_id' => Auth::user()->empresas->first()->id,
                    'licenciamento_id' => null,
                    'processo_id' => $request->input('processo_id'),
                    'fatura_id' => $salesInvoice->id,
                    'status_fatura' => 'emitida'
                ]);
            }

            SalesStatus::create([
                'documentoID' => $salesInvoice->id,
                'invoice_status' => 'N',
                'invoice_status_date' => $request->input('invoice_date'),
                'source_id' => Auth::user()->id,
                'source_billing' => 'P',
                'invoice_available_date' => $request->input('invoice_date'),
            ]);

            // Obter dados da tabela
            $dadosDaTabela = $request->input('dadostabela');

            // Certificar-se de que $dadosDaTabela é uma string antes de tentar decodificar
            if (is_string($dadosDaTabela)) {
                // Remover aspas duplas ao redor da string JSON
                $dadosDaTabela = trim($dadosDaTabela, '"');

                // Decodificar a string JSON para obter um array associativo
                $dadosDecodificados = json_decode($dadosDaTabela, true);

                // Certificar-se de que $dadosDecodificados é um array
                if (is_array($dadosDecodificados)) {
                    // Usar $dadosDecodificados para a lógica de inserção no banco de dados
                    foreach ($dadosDecodificados as $key => $dadosDaLinha) {
                        // Lógica para inserir no banco de dados

                        // Valor total de cada linha
                        $produto_price = str_replace(['Kz', ' '], '', $dadosDaLinha['total']); // Remove Kz e espaços
                        $produto_price = str_replace(',', '.', $produto_price); // Substitui vírgula por ponto
                        $produto_price = floatval($produto_price); // Converte para float

                        $produtoID = Produto::where('ProductCode', $dadosDaLinha['productId'])->first();
                        
                        try {
                            SalesLine::create([
                                'line_number' => $key + 1,
                                'documentoID' => $salesInvoice->id,
                                'productID' => $produtoID->id,
                                'quantity' => $dadosDaLinha['quantidade'],
                                'unit_of_measure' => 'uni',
                                'unit_price' => $dadosDaLinha['preco'],
                                'tax_point_date' => Carbon::now()->toDateTimeString(),
                                'credit_amount' => $produto_price,
                            ]);
                        } catch (QueryException $e) {
                            return DatabaseErrorHandler::handle($e, $request);
                        }
                        

                        // Calculo da taxa de cada produto
                        $tax_produto = ($dadosDaLinha['imposto'] != 0 ? $dadosDaLinha['imposto'] / 100 : 0) * $produto_price;
                        
                        // Somatórios das taxas de cada produto
                        $SumTax += $tax_produto;

                        // Somatório dos preços de produtos sem o iva
                        $SumNetTotal += $produto_price;

                        // Somatório dos preços de produtos com o iva
                        $SumGrossTotal += $produto_price+$tax_produto;
                    } 
                }
            }

            if ($request->input('document_type') == 'FR') {
                SalesDocTotal::create([
                    'tax_payable' => $SumTax, // total das taxas somatório das taxas dos produtos
                    'net_total' => $SumNetTotal, // total de preços sem taxa
                    'gross_total' => $SumGrossTotal, // total de preços com taxa
                    'documentoID' => $salesInvoice->id,
                    'moeda' => 'Kz',
                    'montante_pagamento' => $request->input('invoice_date'),
                    'data_pagamento' => Carbon::now()->toDateTimeString(), // ou 'nullable|date_format:Y-m-d H:i:s',
                ]);
            } elseif($request->input('document_type') == 'FT'){
                SalesDocTotal::create([
                    'tax_payable' => $SumTax, // total das taxas somatório das taxas dos produtos
                    'net_total' => $SumNetTotal, // total de preços sem taxa
                    'gross_total' => $SumGrossTotal, // total de preços com taxa
                    'documentoID' => $salesInvoice->id,
                ]);
            }

            // Assinar o campo Hash
            $this->signAndSaveHash($salesInvoice->id);

            return redirect()->route('documentos.show',$salesInvoice)->with('success', 'Fatura criada com sucesso');

        } catch (QueryException $e) { 

            return DatabaseErrorHandler::handle($e, $request);
        } 
        
    }

    /**
     * Display the specified resource.
     */
    public function show(SalesInvoice $documento){
        $status = SalesStatus::where('documentoID', $documento->id)->first();
        return view('Documentos.detalhe_documento',compact('documento', 'status'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SalesInvoice $documento){

        

        return view('Documentos.ncredito_documento', compact('documento'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SalesInvoice $documento)
    {
        // Criar uma NC (Documento Anulado)
        $invoiceTypeID = (new InvoiceType())->getID($request->input('document_type'));
        $statuss = SalesStatus::where('documentoID', $documento->id)->first();
        $result = DB::select("CALL GenerateInvoiceNo(?,?)", [$invoiceTypeID, Auth::user()->empresas->first()->id]);

        $salesInvoice = SalesInvoice::create([
            'invoice_no' => $result[0]->InvoiceNo,
            'hash' => '0',
            'hash_control' => '0',
            'period' => 1,
            'invoice_date' => Carbon::now()->toDateTimeString(),
            'invoice_date_end' => Carbon::now()->toDateTimeString(),
            'self_billing_indicator' => 0,
            'cash_vat_scheme_indicator' => 0,
            'third_parties_billing_indicator' => 0,
            'invoice_type_id' => $invoiceTypeID,
            'source_id' => Auth::user()->id,
            'system_entry_date' => Carbon::now()->toDateTimeString(), // ou 'nullable|date_format:Y-m-d H:i:s',
            'customer_id' => $documento->customer->id, 
        ]);

        if($statuss->invoice_status == 'N'){
            SalesStatus::where('documentoID', $documento->id)->update([
                'documentoID' => $documento->id,
                'invoice_status' => 'A',
                'invoice_status_date' => Carbon::now()->toDateTimeString(),
                'source_cancel_id' => Auth::user()->id,
                'detalhe' => $request->input('detalhes_fatura'), 
                'motivo'  => $request->input('motivo_devolucao'),
            ]);
        }

        // Assinar o campo Hash
        $this->signAndSaveHash($documento->id);

        return redirect()->route('documentos.show',$salesInvoice)->with('success', 'Factura Anulada com Sucesso');
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SalesInvoice $documento)
    {
        //
    }

    public function ViewPagamento($id){

        // Verificar se o usuario que está em sessão pertence a empresa em sessão
        $salesInvoice = SalesInvoice::findOrFail($id);
        $meios = MetodoPagamento::all();
        return view('Documentos.pagamento', compact('salesInvoice', 'meios'));
    }
}
