<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Helpers\PdfHelper;
use App\Http\Requests\ProcessoRequest;
use App\Models\Customer;
use App\Models\Estancia;
use App\Models\Exportador;
use App\Models\Importacao;
use App\Models\Mercadoria;
use App\Models\Pais;
use App\Models\PautaAduaneira;
use App\Models\Porto;
use App\Models\Processo;
use App\Models\EmolumentoTarifa;
use App\Models\views\ProcessosView;
use App\Models\RegiaoAduaneira;
use App\Models\TipoTransporte;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use PHPJasper\PHPJasper;
use SimpleXMLElement;

class ProcessoController extends Controller
{
    
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $processos = Processo::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('NrProcesso', 'like', "%{$search}%")
                      ->orWhere('CompanyName', 'like', "%{$search}%");
                });
            })
            ->where('empresa_id', Auth::user()->empresas->first()->id ?? null)->get(); // Paginação com 10 itens por página

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
        $paises = Pais::all();
        $estancias = Estancia::all();
        $regioes = RegiaoAduaneira::all();
        $portos = Porto::all();
        $ibans = IbanController::getBankDetails();
        $tipoTransp = TipoTransporte::all();

        // Retornar uma view com o formulário para criar um novo processo
        return view('processos.create', 
        compact(
            'clientes', 
            'exportador',
            'paises', 
            'estancias',
            'regioes',
            'portos',
            'ibans',
            'tipoTransp'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProcessoRequest $request)
    {
        
        try {

            // Dados validados do request
            $processo_request = $request->validated();

            DB::beginTransaction();

            // Verifica o botão clicado para definir a tabela
            $tabela = $request->input('action') === 'draft' ? 'processos_draft' : 'processos';

            // Define a tabela e cria o registro
            $processo = new Processo();
            $processo->setTable($tabela);
            // Cria o processo e obtém a instância completa
            $novoProcesso = $processo->create($processo_request);

            DB::commit();

            if($tabela === 'draft'){
                return redirect()->back()->with('success', 'Salvo como Rascunho!');
            } else{
                // Redirecione para a página de edição de processos com uma mensagem de sucesso
                return redirect()->route('processos.edit', $novoProcesso->id)->with('success', 'Processo inserido com sucesso!');
            }
        } catch (QueryException $e) {
            DB::rollBack();
            return DatabaseErrorHandler::handle($e, $request);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro inesperado: ' . $e->getMessage(),
            ], 500);
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
        $processo = Processo::with('mercadorias')->findOrFail($processoID);

        $pautaAduaneira = PautaAduaneira::all();

        return view('processos.show', compact('processo', 'pautaAduaneira'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $processoID)
    {
        $processo = Processo::findOrFail($processoID);
        $estancias = Estancia::all();
        $regioes = RegiaoAduaneira::all();
        $portos = Porto::all();
        $ibans = IbanController::getBankDetails();
        $tipoTransp = TipoTransporte::all();
        $emolumentoTarifa = EmolumentoTarifa::where('processo_id', $processo->id)->first();
        $clientes = Customer::where('empresa_id', Auth::user()->empresas->first()->id ?? null)->get(); // Busca os Clientes
        $exportador = Exportador::where('empresa_id', Auth::user()->empresas->first()->id ?? null)->get();
        
        $mercadorias = Mercadoria::where('Fk_Importacao', $processo->id)->get(); // Obtenha a relação 'mercadoria'
        $paises = Pais::all();
        return view('processos.edit', compact('processo', 'mercadorias', 'paises', 'estancias',
            'regioes',
            'portos',
            'ibans',
            'tipoTransp',
            'emolumentoTarifa', 'clientes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProcessoRequest $request, $processoID)
    {
        // Dados validados do request
        $processo_requestUP = $request->validated();

        try {
            // Inicia uma transação para garantir a integridade dos dados
            DB::beginTransaction();

            // Verifica se o processo existe
            $processo = Processo::find($processoID);
            if (!$processo) {
                return redirect()->back()->with('error', 'Processo não encontrado');
            }

            // Atualiza os campos do processo
            $processo->update($processo_requestUP);

            DB::commit();

            return redirect()->back()->with('success', 'Dados atualizados com sucesso');

        } catch (QueryException $e) {
            DB::rollBack();

            // Registro do erro
            Log::error('Erro ao atualizar o processo', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Erro ao atualizar os dados. Por favor, tente novamente.');
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

    public function printNotaDespesa($ProcessoID){

        $processo = Processo::where('id', $ProcessoID)->first();
        $emolumentoTarifa = EmolumentoTarifa::where('processo_id', $ProcessoID)->first();

        // Caminho completo para o template .jasper
        $input = base_path('reports/nota_despesa.jrxml'); // Certifique-se de que este arquivo existe
        $output = base_path('reports');

        $params = [
            'Empresa' => Auth::user()->empresas->first()->Empresa,
            'Designacao' => 'Despachante Oficial',
            'Cedula' => Auth::user()->empresas->first()->Cedula,
            'NIF' => Auth::user()->empresas->first()->NIF,
            'P_user' => Auth::user()->name,
            'Endereco_completo' => Auth::user()->empresas->first()->Endereco_completo,
            'Provincia' => Auth::user()->empresas->first()->Provincia,
            'logotipo' => public_path( Auth::user()->empresas->first()->Logotipo),

            // Cliente
            'Cliente' => $processo->cliente->CompanyName,
            'Ref_Cliente' => $processo->cliente->RefCliente,
            'Cli_NIF' => $processo->cliente->CustomerTaxID,

            // Processo
            'ProcessoID' => $processo->id,
            'NrProcesso' => $processo->NrProcesso,
            'ContaDespacho' => $processo->ContaDespacho,
            'Cambio' => $processo->Cambio,
            'ValorAduaneiro' => $processo->ValorAduaneiro,
            'Fob_total' => $processo->Fob_total,
            'Moeda' => $processo->Moeda,
            'NrDU' => $processo->NrDU,
            'N_Dar' => $processo->N_Dar,
            'DataAbertura' => $processo->DataAbertura,

            // Transporte
            'MarcaFiscal'  => $processo->MarcaFiscal,
            'BLC_Porte' => $processo->BLC_Porte,
            'Pais_origem' => $processo->paisOrigem->pais,
            'Pais_destino' => $processo->paisDestino->pais,
            'TipoTransporte' => $processo->tipoTransporte->descricao,
            'registo_transporte' => $processo->registo_transporte,
            'nacionalidade_transporte' => $processo->paisOrigem->pais,
            'DataChegada' => $processo->DataChegada,

            // Tarifas
            'direitos' => $emolumentoTarifa->direitos ?? '0.00',
            'emolumentos' => $emolumentoTarifa->emolumentos  ?? '0.00',
            'porto' => $emolumentoTarifa->porto  ?? '0.00',
            'terminal' => $emolumentoTarifa->terminal  ?? '0.00',
            'lmc' => $emolumentoTarifa->lmc  ?? '0.00',
            'navegacao' => $emolumentoTarifa->navegacao  ?? '0.00',
            'inerentes' => $emolumentoTarifa->inerentes  ?? '0.00',
            'frete' => $emolumentoTarifa->frete  ?? '0.00',
            'carga_descarga' => $emolumentoTarifa->carga_descarga  ?? '0.00',
            'deslocacao' => $emolumentoTarifa->deslocacao  ?? '0.00',
            'selos' => $emolumentoTarifa->selos  ?? '0.00',
            'iva_aduaneiro' => $emolumentoTarifa->iva_aduaneiro  ?? '0.00',
            'iec' => $emolumentoTarifa->iec  ?? '0.00',
            'impostoEstatistico' => $emolumentoTarifa->impostoEstatistico  ?? '0.00',
            'juros_mora' => $emolumentoTarifa->juros_mora  ?? '0.00',
            'caucao' => $emolumentoTarifa->caucao  ?? '0.00',
            'honorario' => $emolumentoTarifa->honorario  ?? '0.00',
            'honorario_iva' => $emolumentoTarifa->honorario_iva  ?? '0.00',
            'orgaos_ofiais' => $emolumentoTarifa->orgaos_ofiais  ?? '0.00',
            'guia_fiscal' => $emolumentoTarifa->guia_fiscal  ?? '0.00',
              
        ];

        // Definir os parâmetros
        $options = [
            'format' => ['pdf'],
            'locale' => 'en',
            'params' => $params,
            'db_connection' => [
                'driver' => env('DB_CONNECTION', 'mysql'),
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'forge'),
                'username' => env('DB_USERNAME', 'forge'),
                'password' => env('DB_PASSWORD', ''),
                'jdbc_driver' => 'com.mysql.cj.jdbc.Driver', // Driver JDBC para MySQL
                'jdbc_url' => 'jdbc:mysql://' . env('DB_HOST') . ':' . env('DB_PORT') . '/' . env('DB_DATABASE'),
            ],
        ];

        $jasper = new PHPJasper();

        $jasper->process(
            $input,
            $output,
            $options
        )->execute();

        $file = $output . '/nota_despesa.pdf';

        if (!file_exists($file)) {
            abort(404);
        }

        return response()->file($file);
    }
}
