<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Models\ContaCorrente;
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
        $privateKeyPath = '/www/wwwroot/aduaneiro.hongayetu.com/ocean_system/sea/weave/fechadura_rest.pem';

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

        //$invoices = SalesInvoice::all();
        $invoices = $this->empresa->Facturas()->with(['Customer', 'InvoiceType', 'salesdoctotal', 'salesstatus.salesInvoice']) 
                    ->orderBy('invoice_date', 'desc')->get();

        $clientes = $this->empresa->customers()->get();

        $faturasPagas = SalesDocTotal::whereNotNull('data_pagamento')->count();
        $faturasPorPagar = SalesDocTotal::whereNull('data_pagamento')->count();
        $faturasEmAtraso = SalesDocTotal::whereNull('data_pagamento')->where('data_pagamento', '<', now())->count();

        return view('Documentos.index', compact('invoices','faturasPagas', 'faturasPorPagar', 'faturasEmAtraso', 'clientes'));
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
        $tipoDocumentos = InvoiceType::all();
        $produtos = Produto::where('ProductType', 'S')->where('ProductGroup', 1)->where('status', 0)->get();
        
        
        // Verifica se o parâmetro 'id' está presente no request
        if ($request->has('licenciamento_id')) {
            $id = $request->input('licenciamento_id');
            $licenciamento = Licenciamento::Find($id);

            $transacoes = ContaCorrente::where('cliente_id', $licenciamento->cliente->id)->orderBy('data', 'desc')->get();
            // Calcular o saldo baseado nas transações
        $saldo = $transacoes->sum(function ($transacao) {
            return $transacao->tipo === 'credito' ? $transacao->valor : -$transacao->valor;
        });
            
            // Retorna a view associada quando o 'id' existe
            return view('Documentos.create_documento', compact('licenciamento', 'produtos', 'tipoDocumentos', 'transacoes', 'saldo'));
        } elseif ($request->has('processo_id')) {
            
            $id = $request->input('processo_id');

            // Busca o processo ou dado relacionado ao id
            $processo = Processo::findOrFail($id); // Encontrar o processo pelo ID

            $transacoes = ContaCorrente::where('cliente_id', $processo->cliente->id)->orderBy('data', 'desc')->get();
            // Calcular o saldo baseado nas transações
        $saldo = $transacoes->sum(function ($transacao) {
            return $transacao->tipo === 'credito' ? $transacao->valor : -$transacao->valor;
        });
            
            // Retorna a view associada quando o 'id' existe
            return view('Documentos.create_documento_processo', compact('processo', 'produtos', 'tipoDocumentos', 'transacoes', 'saldo'));
        } else {
            $empresaId = Auth::user()->empresas->first()->id;
            // Obtém todos os produtos do banco de dados 
            $produtos = Produto::with(['prices', 'grupo'])
            ->where('status', 0) // Apenas produtos ativos
            ->where(function ($query) use ($empresaId) {
                $query->where('empresa_id', $empresaId)->orWhere('empresa_id', 1); // Itens gerais visíveis para todos
            })->get();

            // Retorna a view padrão de criação quando não há 'id'
            $clientes = $this->empresa->customers()->get();

            return view('Documentos.create_documento_2', compact('clientes', 'tipoDocumentos', 'produtos'));
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            // Inicializar as variaveis
            $SumTax = $SumNetTotal = $SumGrossTotal = 0;

            $invoiceTypeID = InvoiceType::where('Code', $request->input('document_type'))->value('Id');
            
            $saldoCliente = $request->input('saldo');
            // Executar o procedimento armazenado para obter o próximo número de fatura
            $result = DB::select("CALL GenerateInvoiceNo(?,?)", [$invoiceTypeID, $this->empresa->id]);

            // Para uma FT
            /*if($request->input('document_type') == 'FT') {
                if($saldoCliente > 0) {
                    return redirect()->back()->with('error', 'O cliente possui um saldo positivo. Não é possível emitir uma factura normal.');
                }elseif($saldoCliente == 0) {
                    return redirect()->back()->with('error', 'O cliente não possui saldo. Não é possível emitir uma factura normal.');
                }elseif($saldoCliente < 0 && $request->input('data_vencimento') != null) {
                    return redirect()->back()->with('error', 'A data de vencimento não deve ser preenchida para facturas normais.');
                }
                return redirect()->back()->with('error', 'A data de vencimento não deve ser preenchida para facturas normais.');
            }
            if($request->input('document_type') == 'FR' && $request->input('data_vencimento') == null) {
                return redirect()->back()->with('error', 'A data de vencimento é obrigatória para facturas a recibo.');
            }
            if($saldoCliente < 0 && $request->input('document_type') == 'FT'){
                return redirect()->back()->with('error', 'O cliente possui um saldo negativo. Não é possível emitir uma factura normal.');
            }*/
            $salesInvoice = SalesInvoice::create([
                'invoice_no' => $result[0]->InvoiceNo,
                'hash' => '0',
                'hash_control' => '0',
                'period' => 1,
                'invoice_date' => $request->input('invoice_date'),
                'invoice_date_end' => $request->input('data_vencimento') ?? Carbon::now()->addMonths(1)->toDateTimeString(),
                'self_billing_indicator' => 0,
                'cash_vat_scheme_indicator' => 0,
                'third_parties_billing_indicator' => 0,
                'invoice_type_id' => $invoiceTypeID,
                'system_entry_date' => Carbon::now()->toDateTimeString(), // ou 'nullable|date_format:Y-m-d H:i:s',
                'transaction_id' => 1,
                'customer_id' => $request->input('customer_id'),
                'source_id' => Auth::user()->id,
                'empresa_id' => Auth::user()->empresas->first()->id,
                'detalhes_factura' => $request->input('detalhes_fatura'),
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

                // Verificar se o cliente tem saldo maior que da factura e criar uma RG automática
                if($saldoCliente >= $SumGrossTotal){
                    // Criar uma RG automática
                    MetodoPagamento::create([
                        'documentoID' => $salesInvoice->id,
                        'data_pagamento' => Carbon::now()->toDateTimeString(),
                        'meio_pagamento' => 'RG',
                        'detalhes' => 'Pagamento automático da factura nº ' . $salesInvoice->invoice_no,
                        'valor' => $SumGrossTotal,
                        'referencia' => 'RG' . $salesInvoice->invoice_no,
                        'source_id' => Auth::user()->id,
                    ]);
                    // Actualizar o saldo do cliente na conta corrente
                    ContaCorrente::create([
                        'cliente_id' => $request->input('customer_id'),
                        'valor' => $SumGrossTotal,
                        'tipo' => 'debito',
                        'descricao' => 'Pagamento automático da factura nº ' . $salesInvoice->invoice_no,
                        'data' => Carbon::now()->toDateTimeString(),
                    ]);
                }elseif($saldoCliente < $SumGrossTotal && $saldoCliente > 0){
                    // Criar uma RG automática com o valor do saldo do cliente
                    MetodoPagamento::create([
                        'documentoID' => $salesInvoice->id,
                        'data_pagamento' => Carbon::now()->toDateTimeString(),
                        'meio_pagamento' => 'RG',
                        'detalhes' => 'Pagamento automático parcial da factura nº ' . $salesInvoice->invoice_no,
                        'valor' => $saldoCliente,
                        'referencia' => 'RG' . $salesInvoice->invoice_no,
                        'source_id' => Auth::user()->id,
                    ]);
                    // Actualizar o saldo do cliente na conta corrente
                    ContaCorrente::create([
                        'cliente_id' => $request->input('customer_id'),
                        'valor' => $saldoCliente,
                        'tipo' => 'debito',
                        'descricao' => 'Pagamento automático parcial da factura nº ' . $salesInvoice->invoice_no,
                        'data' => Carbon::now()->toDateTimeString(),
                    ]);
                }elseif($saldoCliente <= 0){
                    // Não fazer nada
                }
            }

            // Assinar o campo Hash
            $this->signAndSaveHash($salesInvoice->id);

            DB::commit();

            return redirect()->route('documentos.show',$salesInvoice)->with('success', 'Fatura criada com sucesso');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao criar fatura da Empresa: '.$empresa->Empresa.' - '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->back()->with('error', 'Erro ao criar fatura: '.$e->getMessage());
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
        $salesInvoice = SalesInvoice::with('Customer', 'InvoiceType', 'salesdoctotal', 'salesstatus.salesInvoice')
        ->where('empresa_id', Auth::user()->empresas->first()->id)
        ->findOrFail($id);

        // Buscar outras faturas em aberto do mesmo cliente (excluindo a atual)
        $outrasFaturas = SalesInvoice::with('salesdoctotal')
            ->where('customer_id', $salesInvoice->customer_id)
            ->where('id', '!=', $salesInvoice->id)
            ->get();

        $meios = MetodoPagamento::all();

        return view('Documentos.pagamento', compact('salesInvoice', 'outrasFaturas', 'meios'));
    }
}
