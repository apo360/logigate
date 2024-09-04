<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Models\Customer;
use App\Models\Documento;
use App\Models\InvoiceType;
use App\Models\Processo;
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
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Customer::all();

        $newCustomerCode = Customer::generateNewCode();

        $produtos = DB::table('Listar_Produtos')->get();

        $tipoDocumentos = DB::table('InvoiceType')->get();

        return view('Documentos.create_documento_2', compact('clientes', 'tipoDocumentos', 'produtos', 'newCustomerCode'));
        
        /*$processo = Processo::findOrFail($id);
        $produtos = Produto::all();
        return view('Documentos.create_documento_2', compact('processo', 'produtos'));*/
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

                        SalesLine::create([
                            'line_number' => $key + 1,
                            'documentoID' => $salesInvoice->id,
                            'productID' => $dadosDaLinha['productId'],
                            'quantity' => $dadosDaLinha['quantidade'],
                            'unit_of_measure' => 'uni',
                            'unit_price' => $dadosDaLinha['preco'],
                            'tax_point_date' => Carbon::now()->toDateTimeString(),
                            'credit_amount' => $produto_price,
                        ]);

                        // Calculo da taxa de cada produto
                        $tax_produto = ($dadosDaLinha['imposto'] != 0 ? $dadosDaLinha['imposto'] / 100 : 0) * $produto_price;
                        
                        // Somatórios das taxas de cada produto
                        $SumTax += $tax_produto;

                        // Somatório dos preços de produtos sem o iva
                        $SumNetTotal += $produto_price - $tax_produto;

                        // Somatório dos preços de produtos com o iva
                        $SumGrossTotal += $produto_price;
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
                    'payment_mechanism_id' => $request->input('invoice_date'),
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
        return view('Documentos.detalhe_documento',compact('documento'));
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
    public function update(Request $request, Documento $documento)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Documento $documento)
    {
        //
    }
}
