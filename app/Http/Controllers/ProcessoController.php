<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Helpers\PdfHelper;
use App\Http\Requests\DARRequest;
use App\Http\Requests\PortuariaRequest;
use App\Http\Requests\TarifaDURequest;
use App\Models\Customer;
use App\Models\Estancia;
use App\Models\Exportador;
use App\Models\Importacao;
use App\Models\Mercadoria;
use App\Models\Pais;
use App\Models\PautaAduaneira;
use App\Models\Porto;
use App\Models\Processo;
use App\Models\views\ProcessosView;
use App\Models\RegiaoAduaneira;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

class ProcessoController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $processos = ProcessosView::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('NrProcesso', 'like', "%{$search}%")
                      ->orWhere('CompanyName', 'like', "%{$search}%");
                });
            })
            ->where('IdEmpresa', Auth::user()->empresas->first()->id ?? null)->get(); // Paginação com 10 itens por página

        return view('processos.index', compact('processos'));
    }

    public function buscarProcesso(Request $request)
    {
        $numeroProcesso = $request->input('processo_search');
        $processo = Processo::where('NrProcesso', $numeroProcesso)->first();

        if ($processo) {
            $mercadorias = Mercadoria::where('Fk_Importacao', $processo->importacao->id)->get();
            $mercadoriasAgrupadas = $mercadorias->groupBy('codigo_aduaneiro');
        } else {
            $mercadorias = collect(); // Retorna uma coleção vazia se não houver processo encontrado
            $mercadoriasAgrupadas = collect();
        }

        $pautaAduaneira = PautaAduaneira::all();

        return view('processos.du', compact('mercadorias', 'processo','numeroProcesso', 'pautaAduaneira', 'mercadoriasAgrupadas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Customer::where('empresa_id', Auth::user()->empresas->first()->id ?? null)->get(); // Busca os Clientes
        $exportador = Exportador::where('empresa_id', Auth::user()->empresas->first()->id ?? null)->get();
        $NewProcesso = Processo::generateNewProcesso(); // Inicializar com novo código de processo
        $paises = Pais::all();
        $estancias = Estancia::all();
        $regioes = RegiaoAduaneira::all();
        $portos = Porto::all();
        
        // chamar a stored procedure
        $newCustomerCode = Customer::generateNewCode();
        $newExportadorCode = Exportador::generateNewCode();

        // Retornar uma view com o formulário para criar um novo processo
        return view('processos.create', 
        compact(
            'clientes', 
            'exportador', 
            'NewProcesso', 
            'paises', 
            'newCustomerCode', 
            'newExportadorCode',
            'estancias',
            'regioes',
            'portos'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        try {

            DB::beginTransaction();
            // Obtém o usuário autenticado
            $user = Auth::user();

            $processo_request =  $request->validate([
                'NrProcesso' => 'required|string|max:100',
                'ContaDespacho' => 'nullable|string|max:150',
                'customer_id' => 'required|string|max:30',
                'RefCliente' => 'nullable|string|max:200',
                'Descricao' => 'nullable|string|max:200',
                'DataAbertura' => 'required|date',
                'TipoProcesso' => 'required|string|max:100',
                'Situacao' => 'required|string',
                'exportador_id' => 'required|string',
                'estancia_id' => 'required|string',
            ]);
    
            $processo_request['user_id'] = $user->id;
            $processo_request['empresa_id'] = $user->empresas->first()->id;

            // Cria o processo
            $processo = Processo::create($processo_request);

            // Cria a importação
            Importacao::create([
                'processo_id' => $processo->id,
                'Fk_pais_origem' => $request->input('Fk_pais'),
                'PortoOrigem' => $request->input('PortoOrigem'),
                'TipoTransporte' => $request->input('TipoTransporte'),
                'NomeTransporte' => $request->input('NomeTransporte'),
                'DataChegada' => $request->input('DataChegada'),
            ]);

            DB::commit();

            // Redirecione para a página de listagem de processos com uma mensagem de sucesso
            return redirect()->route('processos.edit', $processo->id)->with('success', 'Processo inserido com sucesso!');
        } catch (QueryException $e) {
            return DatabaseErrorHandler::handle($e, $request);
        }
    }

    /**
     * Display the specified resource.
     */
    /**
     * Display the specified resource.
     */
    public function show($processoID)
    {
        $processo = Processo::findOrFail($processoID);

        if ($processo) {
            $mercadorias = Mercadoria::where('Fk_Importacao', $processo->importacao->id)->get();
            $mercadoriasAgrupadas = $mercadorias->groupBy('codigo_aduaneiro');
        } else {
            $mercadorias = collect(); // Retorna uma coleção vazia se não houver processo encontrado
            $mercadoriasAgrupadas = collect();
        }

        $pautaAduaneira = PautaAduaneira::all();

        return view('processos.show', compact('processo', 'mercadorias', 'pautaAduaneira', 'mercadoriasAgrupadas'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $processoID)
    {
        $processo = Processo::findOrFail($processoID);
        
        $mercadorias = Mercadoria::where('Fk_Importacao', $processo->importacao->id)->get(); // Obtenha a relação 'mercadoria'
        $paises = Pais::all();
        return view('processos.edit', compact('processo', 'mercadorias', 'paises'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $ProcessoRequest, TarifaDURequest $DURequest, PortuariaRequest $Portuaria, $processoID, DARRequest $DARRequest)
    {
        
        try {

            // Inicia uma transação para garantir a integridade dos dados
            DB::beginTransaction();

            // Actualizar os campos de processos...
            Processo::where('id', $processoID)->update([
                'Situacao' => $ProcessoRequest->input('Situacao'),
            ]); // Dados do Processo

            Importacao::where('processo_id', $processoID)->update([
                'MarcaFiscal' => $ProcessoRequest->input('MarcaFiscal'),
                'BLC_Porte' => $ProcessoRequest->input('BLC_Porte'),
                'Moeda' => $ProcessoRequest->input('Moeda'),
                'FOB' => $ProcessoRequest->input('FOB'), 
                'Freight' => $ProcessoRequest->input('Freight'), //Frete
                'Insurance' => $ProcessoRequest->input('Insurance'), // Seguro
                'Cambio' => $ProcessoRequest->input('Cambio'),
                'ValorAduaneiro' => $ProcessoRequest->input('ValorAduaneiro'),
                'ValorTotal' => $ProcessoRequest->input('ValorTotal'),
            ]); // Dados do Importação

            TarifaDARController::storeOrUpdate($DARRequest, $processoID); //Tarifas do DAR

            TarifaPortuariaController::storeOrUpdate($Portuaria, $processoID); //Tarifas Portuarias

            TarifaDUController::storeOrUpdate($DURequest, $processoID); //Tarifas do DU

            // Caso exista documento(File/Files) para inserir ou actualizar deve activar a função

            
            DB::commit();

            return redirect()->back()->with('success', 'Dados Actualizados com sucesso');

        } catch (QueryException $e) {

            DB::rollBack();

            return DatabaseErrorHandler::handle($e, $ProcessoRequest);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $processo = Processo::find($id);
        if ($processo) {
            $processo->delete();
            return redirect()->route('processos.index')->with('success', 'Processo excluído com sucesso!');
        }
        return redirect()->route('processos.index')->with('error', 'Processo não encontrado.');
    }


    public function print($processoID)
    {
        $processo = Processo::where('ProcessoID', $processoID)->first();

        $header_footer = new PdfHelper();
        $header_footer::generatePrint($processo->cliente->Id);

        // Importante: Não é necessário retornar nada nesta rota
    }

    public function tarifas()
    {
        // Lógica para calcular impostos com base nos dados do formulário
        return view('processos.tarifas');
    }

    public function rastreamento(){
        return view('processos.rastreamento');
    }

    public function autorizacao(){

        return view('processos.autorizacao_regulamentacao');
    }

    public function du_electronico(){
        return view('processos.du');
    }

    public function atualizarCodigoAduaneiro(Request $request)
    {
        $data = $request->validate([
            'mercadoria_id' => 'required|array',
            'mercadoria_id.*' => 'exists:mercadorias,id',
            'codigo_aduaneiro' => 'required|array',
            'codigo_aduaneiro.*' => 'string|max:255',
        ]);

        foreach ($data['mercadoria_id'] as $index => $id) {
            $mercadoria = Mercadoria::find($id);
            if ($mercadoria) {
                $mercadoria->codigo_aduaneiro = $data['codigo_aduaneiro'][$index];
                $mercadoria->save();
            }
        }

        return redirect()->back()->with('success', 'Códigos aduaneiros atualizados com sucesso!');
    }

    public function GerarXml($IdProcesso) {

        $processo = Processo::where('id', $IdProcesso)->first();
        $mercadorias = Mercadoria::where('Fk_Importacao', $processo->importacao->id)->get();
        $mercadoriasAgrupadas = $mercadorias->groupBy('codigo_aduaneiro');
        $pautaAduaneira = PautaAduaneira::all();

        $xml = new SimpleXMLElement('<MercadoriasAgrupadas/>');
    
        foreach ($mercadoriasAgrupadas as $codigoAduaneiro => $mercadorias) {
            $codigoNode = $xml->addChild('CodigoAduaneiro');
            $codigoNode->addAttribute('codigo', $codigoAduaneiro);
            $descricao = $pautaAduaneira->firstWhere('codigo', $codigoAduaneiro)->descricao;
            $codigoNode->addChild('Descricao', $descricao);
    
            $quantidadeTotal = 0;
            $fobTotal = 0;
    
            foreach ($mercadorias as $mercadoria) {
                $mercadoriaNode = $codigoNode->addChild('Mercadoria');
                $mercadoriaNode->addChild('Descricao', $mercadoria->Descricao);
                $mercadoriaNode->addChild('Quantidade', $mercadoria->Quantidade);
                $mercadoriaNode->addChild('PrecoUnitario', $mercadoria->preco_unitario);
                $mercadoriaNode->addChild('FOB', $mercadoria->preco_total);
    
                $quantidadeTotal += $mercadoria->Quantidade;
                $fobTotal += $mercadoria->preco_total;
            }
    
            $codigoNode->addChild('QuantidadeTotal', $quantidadeTotal);
            $codigoNode->addChild('FOBTotal', $fobTotal);
        }
    
        $xmlString = $xml->asXML();
        $filename = 'mercadorias_agrupadas_' . date('Ymd_His') . '.xml';
        Storage::put('public/' . $filename, $xmlString);
    
        return response()->download(storage_path('app/public/' . $filename));
    }

    public function GerarTxT($IdProcesso)
    {
        // Buscando o processo pelo ID
        $processo = Processo::findOrFail($IdProcesso);
        $importacao = $processo->importacao;  // Supondo que importacao seja um relacionamento
        $mercadorias = $importacao->mercadorias;  // Supondo que mercadorias seja um relacionamento

        $peso_bruto = 0;
        $FOB = $importacao->FOB;
        $Frete = $importacao->Freight;
        $Seguro = $importacao->Insurance;
        $CIF = $FOB + $Frete + $Seguro;

        // Linha 0 - Cabeçalho do processo
        $linha0 = "0|" . count($mercadorias) . "|{$processo->estancia_id}|{$processo->cliente->CompanyName}|{$processo->empresa->Empresa}|{$processo->empresa->Cedula}|{$processo->empresa->Email}|{$processo->RefCliente}|||||||||||||||||||||||||||||";
        
        // Linha 2 - Adições de mercadorias
        $adicoes = [];
        foreach ($mercadorias as $key => $adicao) {
            $ordem = $key + 1;
            $peso_bruto += $adicao->Peso;
            
            // Calculando Frete e Seguro proporcionais
            $frete_seguro = Mercadoria::calcularFreteMercadoria($adicao->preco_total, $FOB, $Frete) 
                        + Mercadoria::calcularSeguroMercadoria($adicao->preco_total, $FOB, $Seguro);
            
            // Criando a linha de adição
            $adicoes[] = "2|{$ordem}|||||{$adicao->codigo_aduaneiro}|{$adicao->Quantidade}||{$importacao->origem->codigo}|{$adicao->Peso}|{$importacao->Moeda}|{$adicao->preco_total}|{$frete_seguro}|{$CIF}|||{$adicao->Unidade}|||||||||||||||||||";
        }

        // Linha 1 - Informações do exportador e transporte
        $linha1 = "1|{$processo->exportador->ExportadorTaxID}|{$processo->exportador->Exportador}|{$processo->cliente->CustomerTaxID}||{$processo->empresa->Cedula}|{$importacao->TipoTransporte}||||A19 32077|//|LAD|{$processo->TipoDocumento}|{$processo->estancia->cod_estancia}|" . count($mercadorias) . "|{$peso_bruto}||||GATT|RD|051|F|1|{$processo->Descricao}||||{$importacao->origem->codigo}{$importacao->PortoOrigem}|{$importacao->origem->codigo}|AO||||";
        

        // Montando o conteúdo completo
        $conteudo = $linha0 . "\n" . $linha1 . "\n" . implode("\n", $adicoes);

        // Nome do arquivo
        $nomeArquivo = 'licenciamento_' . $processo->NrProcesso . '.txt';

        // Criando e retornando o arquivo .txt para download
        return response($conteudo)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="'.$nomeArquivo.'"');
    }

    public function getProcessesByIdAndStatus($ProcessoId, $status)
    {
        // Find processes with the specified customer ID and status
        $processos = Processo::where('ProcessoID', $ProcessoId)->where('Situacao', $status)->get();
    
        // You can return the processes as a JSON response
        return response()->json([
            'processos' => $processos,
            'cliente' => $processos->first()->cliente, // Assuming all processes belong to the same customer
            'mercadorias' => $processos->flatMap->mercadorias,
            'cobranca' => $processos->first()->cobrado
        ]);
    }
}
