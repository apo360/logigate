<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Helpers\PdfHelper;
use App\Http\Requests\ProcessoRequest;
use App\Models\ContaCorrente;
use App\Models\Customer;
use App\Models\Documento;
use App\Models\Estancia;
use App\Models\Exportador;
use App\Models\Mercadoria;
use App\Models\Pais;
use App\Models\PautaAduaneira;
use App\Models\Porto;
use App\Models\Processo;
use App\Models\EmolumentoTarifa;
use App\Models\MercadoriaAgrupada;
use App\Models\ProcessoDraft;
use App\Models\views\ProcessosView;
use App\Models\RegiaoAduaneira;
use App\Models\TipoTransporte;
use App\Models\CondicaoPagamento;
use App\Models\MercadoriaLocalizacao;
use Carbon\Carbon;
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
    protected $empresa;
    // constructor with auth middleware
    public function __construct()
    {
        $this->empresa = Auth::user()->empresas->first();
    }
    
    function numeroParaExtenso($numero) {
        $unidades = ['', 'um', 'dois', 'três', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove'];
        $dezenas = ['', 'dez', 'vinte', 'trinta', 'quarenta', 'cinquenta', 'sessenta', 'setenta', 'oitenta', 'noventa'];
        $dez_a_vinte = ['dez', 'onze', 'doze', 'treze', 'quatorze', 'quinze', 'dezesseis', 'dezessete', 'dezoito', 'dezenove'];
        $centenas = ['', 'cem', 'duzentos', 'trezentos', 'quatrocentos', 'quinhentos', 'seiscentos', 'setecentos', 'oitocentos', 'novecentos'];
        $milhares = ['mil', 'milhão', 'bilhão'];
    
        // Formatar número para ter sempre duas casas decimais
        $numero = number_format($numero, 2, '.', '');
        list($inteiro, $centavos) = explode('.', $numero);
        $inteiro = intval($inteiro);
        $centavos = intval($centavos);
    
        // Função interna para converter um número menor que 1000
        function converteMenorQueMil($num) {
            global $unidades, $dezenas, $dez_a_vinte, $centenas;
            $extenso = '';
    
            if ($num == 100) return 'cem';
    
            if ($num >= 100) {
                $extenso .= $centenas[intval($num / 100)];
                $num %= 100;
                if ($num > 0) $extenso .= ' e ';
            }
    
            if ($num >= 10 && $num <= 19) {
                $extenso .= $dez_a_vinte[$num - 10];
            } else {
                if ($num >= 20) {
                    $extenso .= $dezenas[intval($num / 10)];
                    $num %= 10;
                    if ($num > 0) $extenso .= ' e ';
                }
                if ($num > 0) {
                    $extenso .= $unidades[$num];
                }
            }
    
            return $extenso;
        }
    
        // Converter a parte inteira
        $partes = [];
        $milharesIndex = 0;
    
        while ($inteiro > 0) {
            $parte = $inteiro % 1000;
            if ($parte > 0) {
                $prefixo = converteMenorQueMil($parte);
                if ($milharesIndex > 0) {
                    if ($parte == 1 && $milharesIndex == 1) {
                        $prefixo = 'mil';
                    } else {
                        $prefixo .= ' ' . $milhares[$milharesIndex - 1];
                    }
                }
                array_unshift($partes, $prefixo);
            }
            $inteiro = intval($inteiro / 1000);
            $milharesIndex++;
        }
    
        $extenso = implode(' ', $partes) . ' Kwanzas';
    
        // Adicionar os centavos, se houver
        if ($centavos > 0) {
            $extenso .= ' e ' . converteMenorQueMil($centavos) . ' cêntimos';
        }
    
        return ucfirst($extenso);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $empresa = Auth::user()->empresas->first();
        
        if (!$empresa) {
            return redirect()->back()->with('error', 'Nenhuma empresa associada.');
        }

        $processos = Processo::query()
            ->where('empresa_id', $empresa->id)
            ->when($request->input('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('NrProcesso', 'like', "%{$search}%")
                    ->orWhere('CompanyName', 'like', "%{$search}%")
                    ->orWhere('DataAbertura', 'like', "%{$search}%");
                });
            })
            ->orderBy('DataAbertura', 'desc')->get(); // Paginação correta
        return view('processos.index', compact('processos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = $this->empresa->customers()->get(); // Busca os Clientes
        $exportador = $this->empresa->exportadors()->get();
        $paises = Pais::all();
        $estancias = Estancia::all();
        $regioes = RegiaoAduaneira::all();
        $paises_porto = Porto::select('pais', 'pais_id')->distinct()->whereNotNull('pais')->orderBy('pais')->get();
        $portos = Porto::all();
        $ibans = IbanController::getBankDetails();
        $tipoTransp = TipoTransporte::all();
        $processos_drafts = ProcessoDraft::where('empresa_id', $this->empresa->id)->orderBy('DataAbertura', 'desc')->get();
        $condicoes_pagamento = CondicaoPagamento::all();
        $localizacoes = MercadoriaLocalizacao::all();

        // Retornar uma view com o formulário para criar um novo processo
        return view('processos.create', 
        compact(
            'clientes', 
            'exportador',
            'paises', 
            'estancias',
            'regioes',
            'paises_porto',
            'portos',
            'ibans',
            'tipoTransp',
            'processos_drafts',
            'condicoes_pagamento',
            'localizacoes',
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

            // Cria o processo e obtém a instância completa
            $novoProcesso = Processo::create($processo_request);

            // Após cria o processos definitivo, deve apagar o rascunho

            // Verifica se há um rascunho para excluir
            if ($request->filled('id_rascunho')) {
                $rascunho = $request->input('id_rascunho');

                // Verifica se o rascunho existe antes de tentar excluí-lo
                if (ProcessoDraft::find($rascunho)) { ProcessoDraft::destroy($rascunho); } else {
                    Log::warning("Tentativa de excluir rascunho inexistente com ID: {$rascunho}");
                }
            }
            /*$rascunho = $request->input('id_rascunho');
            $draft = new ProcessoDraftController();
            $draft->destroy($rascunho);*/

            DB::commit();

            // Redirecione para a página de edição de processos com uma mensagem de sucesso
            return redirect()->route('processos.edit', $novoProcesso->id)->with('success', 'Processo inserido com sucesso!');

        } catch (QueryException $e) {
            DB::rollBack();
            return DatabaseErrorHandler::handle($e, $request);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro inesperado: ' . $e->getMessage(),], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($processoID)
    {
        $processo = Processo::with('mercadorias')->findOrFail($processoID);
        $mercadoriasAgrupadas = MercadoriaAgrupada::with('mercadorias')->where('processo_id',$processoID)->get();

        $pautaAduaneira = PautaAduaneira::all();
        $transacoes = ContaCorrente::where('cliente_id', $processo->cliente->id)->orderBy('data', 'desc')->get();

        

        // Calcular o saldo baseado nas transações
        $saldo = $transacoes->sum(function ($transacao) {
            return $transacao->tipo === 'credito' ? $transacao->valor : -$transacao->valor;
        });

        $camposImportantes = [
            'estancia_id' => 'Estância Aduaneira',
            'porto_desembarque_id' => 'Porto de Desembarque',
            'localizacao_mercadoria_id' => 'Localização da Mercadoria',
            'regime_aduaneiro' => 'Regime Aduaneiro',
            'fob_total' => 'Valor FOB',
            'Pais_origem' => 'País de Origem',
        ];

        $camposNaoPreenchidos = $processo->getCamposNaoPreenchidos($camposImportantes);

        return view('processos.show', compact('processo', 'pautaAduaneira', 'mercadoriasAgrupadas', 'saldo', 'camposImportantes'));
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
        $localizacoes = MercadoriaLocalizacao::all();
        
        $mercadorias = Mercadoria::where('Fk_Importacao', $processo->id)->get(); // Obtenha a relação 'mercadoria'
        $paises = Pais::all();
        return view('processos.edit', compact('processo', 'mercadorias', 'paises', 'estancias',
            'regioes',
            'portos',
            'ibans',
            'tipoTransp',
            'emolumentoTarifa', 'clientes', 'localizacoes'));
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
     * Função para finalizar o processo
     */
    public function processoFinalizar($processoID)
    {
        $processo = Processo::find($processoID);

        $emolumentoTarifa = EmolumentoTarifa::where('processo_id', $processo->id)->first();

        // Verificar se o processo existe
        if (!$processo) {
            Log::error('Processo não encontrado.', [
                'processo_id' => $processoID,
                'user_id' => Auth::user()->id,
                'timestamp' => now(),
            ]);
            return response()->json(['error' => 'Processo não encontrado'], 404);
        }

        // Validar os campos obrigatórios
        $erros = [];
        if (empty($processo->NrDU)) {
            $erros[] = 'O campo NrDU é obrigatório.';
        }
        if (empty($processo->BLC_Porte)) {
            $erros[] = 'O campo BLC_Porte é obrigatório.';
        }
        if (empty($processo->ValorAduaneiro)) {
            $erros[] = 'O campo ValorAduaneiro é obrigatório.';
        }
        if (empty($processo->cif)) {
            $erros[] = 'O campo CIF é obrigatório.';
        }
        if (empty($processo->Cambio)) {
            $erros[] = 'O campo Cambio é obrigatório.';
        }

        // Verificar se há pelo menos uma mercadoria
        if ($processo->mercadorias()->count() == 0) {
            $erros[] = 'Deve haver pelo menos uma mercadoria associada ao processo.';
        }

        // Verificar o emolumentoTarifa->honorario
        if (is_null($emolumentoTarifa->honorario) || $emolumentoTarifa->honorario < 0) {
            $erros[] = 'Os campos Honorários e Emolumentos Tarifa não podem ser nulo ou negativo.';
        }

        // Retornar erros, se existirem
        if (count($erros) > 0) {
            // Registrar os erros nos logs
            Log::error('Erros ao finalizar o processo:', [
                'processo_id' => $processoID, // Supondo que você tenha o ID do processo
                'erros' => $erros,
                'user_id' => Auth::user()->id ?? 'Usuário não autenticado',
                'timestamp' => now()
            ]);
            return response()->json(['errors' => $erros], 422);
            
        }

        // Atualizar o estado para finalizado
        $processo->Estado = 'finalizado';
        $processo->DataFecho = now();

        // Gerar o próximo número de ContaDespacho
        $processo->ContaDespacho = $this->gerarContaDespachoSequencial();

        // Salvar as alterações
        $processo->save();

        return response()->json(['message' => 'Processo finalizado com sucesso'], 200);
    }

    /**
     * Gera o próximo número sequencial para o campo ContaDespacho
     *
     * @return string O novo valor para ContaDespacho no formato CCD-xxx/ano
     */
    private function gerarContaDespachoSequencial()
    {
        $anoCorrente = date('Y'); // Obtém o ano atual
        $ultimaConta = Processo::whereYear('created_at', $anoCorrente)
            ->whereNotNull('ContaDespacho')
            ->orderByRaw("CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(ContaDespacho, '/', 1), '-', -1) AS UNSIGNED) DESC")
            ->first();

        // Determinar o próximo número sequencial
        $sequencial = 1;
        if ($ultimaConta) {
            // Extrair o número sequencial da última ContaDespacho
            preg_match('/\d+/', explode('/', $ultimaConta->ContaDespacho)[0], $match);
            $sequencial = isset($match[0]) ? (int) $match[0] + 1 : 1;
        }

        // Formatar o número sequencial com zeros à esquerda
        $numeroFormatado = str_pad($sequencial, 3, '0', STR_PAD_LEFT);

        // Retornar o formato completo da ContaDespacho
        return "CCD-{$numeroFormatado}/{$anoCorrente}";
    }

    /**
     * Função que permite listar todos os processos em condições de finalizar, mas não estão finalizados
     */
    public function processosNaoFinalizados()
    {
        $processos = Processo::whereNotNull('NrDU')
            ->whereNotNull('BLC_Porte')
            ->whereNotNull('ValorAduaneiro')
            ->whereNotNull('cif')
            ->whereNotNull('Cambio')
            ->whereHas('mercadorias')
            ->whereHas('emolumentoTarifa', function ($query) {
                $query->whereNotNull('honorario')->where('honorario', '>=', 0);
            })
            ->where('Estado', '!=', 'finalizado')->where('empresa_id', Auth::user()->empresas->first()->id)
            ->get();

        return response()->json($processos, 200);
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
    /**
     * Função para calcular as tarifas e impostos
     */
    public function tarifas()
    {
        // Lógica para calcular impostos com base nos dados do formulário
        // Buscar todas as mercadorias com seus processos e códigos aduaneiros
        $mercadorias = Mercadoria::with('processos', 'pautaAduaneira')->get();

        $impostos = $mercadorias->map(function ($mercadoria) {
            $pauta = $mercadoria->pautaAduaneira;

            if (!$pauta) {
                return null; // Se não houver pauta, ignora o cálculo
            }

            // Definição das taxas e valores base
            $valorBase = $mercadoria->valor; // Assumindo que cada mercadoria tem um valor atribuído
            $taxaImportacao = (float) $pauta->rg;
            $iva = (float) $pauta->iva;
            $ieq = (float) $pauta->ieq;

            // Cálculo dos tributos
            $valorImportacao = ($taxaImportacao / 100) * $valorBase;
            $valorIva = ($iva / 100) * ($valorBase + $valorImportacao);
            $valorIeq = ($ieq / 100) * $valorBase;
            $totalTributos = $valorImportacao + $valorIva + $valorIeq;

            return [
                'codigo' => $pauta->getCodigoSemPontosAttribute(),
                'descricao' => $mercadoria->Descricao,
                'taxa_importacao' => $taxaImportacao,
                'iva' => $iva,
                'ieq' => $ieq,
                'valor_base' => $valorBase,
                'total_tributos' => $totalTributos
            ];
        })->filter(); // Remove valores nulos

        return view('processos.tarifas', compact('impostos'));
    }
    // -------------------------------   Blocos para ser eliminados ---------------------------------------- //

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

    // -------------------------------   */Blocos para ser eliminados/* ---------------------------------------- //

    /**
     * Metodo para imprimir Nota de Despesas do Processo
     */
    public function printNotaDespesa($ProcessoID){

        $processo = Processo::where('id', $ProcessoID)->first();
        $emolumentoTarifa = EmolumentoTarifa::where('processo_id', $ProcessoID)->first();

        // Caminho completo para o template .jasper
        $input = base_path('reports/nota_despesa.jrxml'); // Certifique-se de que este arquivo existe
        $output = base_path('reports');

        $logoPath = public_path('logos/' . Auth::user()->empresas->first()->Logotipo);

        $params = [
            'Empresa' => Auth::user()->empresas->first()->Empresa,
            'Designacao' => 'Despachante Oficial',
            'Cedula' => Auth::user()->empresas->first()->Cedula,
            'NIF' => Auth::user()->empresas->first()->NIF,
            'P_user' => Auth::user()->name,
            'Endereco_completo' => Auth::user()->empresas->first()->Endereco_completo  ?? '',
            'Provincia' => Auth::user()->empresas->first()->provincia->Nome  ?? '',
            'logotipo' => $logoPath,

            // Cliente
            'Cliente' => $processo->cliente->CompanyName  ?? '',
            'Ref_Cliente' => $processo->cliente->RefCliente  ?? '',
            'Cli_NIF' => $processo->cliente->CustomerTaxID  ?? '',

            // Processo
            'ProcessoID' => $processo->id,
            'NrProcesso' => $processo->NrProcesso ?? '',
            'ContaDespacho' => $processo->ContaDespacho  ?? '',
            'Cambio' => $processo->Cambio ?? '0.00',
            'ValorAduaneiro' => $processo->ValorAduaneiro  ?? '0.00',
            'Fob_total' => $processo->cif  ?? '0.00',
            'Moeda' => $processo->Moeda  ?? '',
            'NrDU' => $processo->NrDU  ?? '',
            'N_Dar' => $processo->N_Dar  ?? '',
            'DataAbertura' => $processo->DataAbertura ?? '',

            // Transporte
            'MarcaFiscal'  => $processo->MarcaFiscal  ?? '',
            'BLC_Porte' => $processo->BLC_Porte  ?? '',
            'Pais_origem' => $processo->paisOrigem->pais ?? '',
            'Pais_destino' => $processo->paisDestino->pais ?? '',
            'TipoTransporte' => $processo->tipoTransporte->descricao ?? '',
            'registo_transporte' => $processo->registo_transporte  ?? '',
            'nacionalidade_transporte' => $processo->paisOrigem->pais  ?? '',
            'DataChegada' => $processo->DataChegada ?? '',

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
            'guia_fiscal' => $emolumentoTarifa->guia_fiscal ?? '0.00',   /// Esta linha deve ser o somatório de todas as linhas acima
              
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

        $jasper->process($input, $output, $options)->execute();

        $file = $output . '/nota_despesa.pdf';

        if (!file_exists($file)) {abort(404);}

        return response()->file($file);
    }

    /**
     * Metodo para imprimir a Carta Diversa do Processos
     */
    public function printCartaDiversa(Request $request, $ProcessoID)
    {
        $request->merge([
            'valor' => (float) str_replace(',', '.', $request->valor),
            'saldoCliente' => (float) str_replace(',', '.', $request->saldoCliente),
            'fobTotal' => (float) str_replace(',', '.', $request->fobTotal),
        ]);
        // Validar dados recebidos
        $request->validate([
            'valor' => 'required|numeric|min:0',
            'saldoCliente' => 'required|numeric',
            'fobTotal' => 'required|numeric|min:0',
            'emitirComFatura' => 'nullable|boolean',
        ]);

        // Caminho completo para o template .jasper
        $input = base_path('reports/Requisicao.jrxml'); // Certifique-se de que este arquivo existe
        $output = base_path('reports');

        // Identificar se o Cliente e o Processos Existem
        $processo = Processo::findOrFail($ProcessoID);
        $clienteID = $processo->cliente->id;

        // Pegar saldo Anterior
        $saldoClienteAnterior = (float) $request->saldoCliente;

        // Variaveis para inserir na conta corrente
        ContaCorrente::create([
            'cliente_id' => $clienteID,
            'valor' => $request->valor,
            'tipo' => 'debito',
            'descricao' => 'Desconto Refrente ao Processo Nº : '.$processo->NrProcesso,
            'data' => now(),
        ]);

       // Obter saldo Actual
       $saldoClienteActual = ContaCorrente::where('cliente_id', $clienteID)->get()
       ->sum(fn ($transacao) => $transacao->tipo === 'credito' ? $transacao->valor : -$transacao->valor);

        // Verificar se o Usuario Quer facturas
        if($request->emitirComFatura){
            // Verificar condições de saldo para emissão de facturas
            $valorPagar = (float) $request->valor;

            // Se o valor cobre
            if($saldoClienteAnterior >= $valorPagar){
                // Emitir uma Factura recibo (FR Paga)
                $this->emitirFatura($clienteID, $valorPagar, 'FR', $processo->NrProcesso);
            }

            // Se saldo menor que 0
            elseif($saldoClienteAnterior <= 0){
                // Emitir uma Factura Comercial (FT)
                $this->emitirFatura($clienteID, $valorPagar, 'FT', $processo->NrProcesso);
            }

            // Se saldo menor que o valor a pagar e maior que zero
            elseif($saldoClienteAnterior > 0 && $saldoClienteAnterior < $valorPagar){
                // Emitir duas Facturas uma FR e FT
                $valorFT = $valorPagar - $saldoClienteAnterior; // Factura de Divida
                $this->emitirFatura($clienteID, $saldoClienteAnterior, 'FR', $processo->NrProcesso);
                $this->emitirFatura($clienteID, $valorFT, 'FT', $processo->NrProcesso);
            }

        }

        // Função para transformar o numerario em descritivo (Valor ($request->valor))
        $descricaoValorPagar = $this->numeroParaExtenso($request->valor);

        // Enviar Parametros para Report
        $params = [
            'ProcessoID' => $processo->id,
            'saldo' => $saldoClienteAnterior,
            'saldoActual' => $saldoClienteActual,
            'descricao' => $descricaoValorPagar,
            'logotipo' => Auth::user()->empresas->first()->Logotipo,
        ];

        $jasper = new PHPJasper();

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

        $jasper->process(
            $input,
            $output,
            $options
        )->execute();

        $file = $output . '/Requisicao.pdf';

        if (!file_exists($file)) {
            abort(404);
        }

        return response()->file($file);
        
    }

    // Metodo para enviar os parametros e emitir uma factura
    private function emitirFatura($clienteID, $valor, $tipo, $processo)
    {
        // Simulação de emissão de fatura (pode ser ajustado conforme sua lógica)
        /*Documento::create([
            'cliente_id' => $clienteID,
            'valor' => $valor,
            'tipo' => $tipo, // 'FR' ou 'FT'
            'descricao' => "Fatura $tipo referente ao Processo Nº: $processo",
            'data' => now(),
        ]);*/
    }

    public function printExtratoMercadoria($ProcessoID){

        $processo = Processo::where('id', $ProcessoID)->first();

        // Caminho completo para o template .jasper
        $input = base_path('reports/extrato_mercadoria.jrxml'); // Certifique-se de que este arquivo existe
        $output = base_path('reports');

        // Definir os parâmetros
        $options = [
            'format' => ['pdf'],
            'locale' => 'en',
            'params' => ['id' => $processo->id],
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

        $jasper->process($input, $output, $options)->execute();

        $file = $output . '/extrato_mercadoria.pdf';

        if (!file_exists($file)) {abort(404);}

        return response()->file($file);
    }

    // Estrutura Basica
    public function gerarXML($ProcessoID)
    {
        // Obter informações do processo
        $processo = Processo::with('exportador', 'mercadorias')->where('id', $ProcessoID)->first();

        if (!$processo) {
            return response()->json(['error' => 'Processo não encontrado'], 404);
        }

        // Criar o XML com cabeçalho UTF-8
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="no"?><ASYCUDA></ASYCUDA>');

        // Adicionar identificação
        $this->adicionarIdentificacaoXML($xml, $processo);

        // Traders
        $trader = $xml->addChild('Traders');

        // Exportador
        $exporter = $trader->addChild('Exporter');
        $exporter->addChild('Exporter_code', optional($processo->exportador)->ExportadorTaxID ?? '');
        $exporter->addChild('Exporter_name', htmlspecialchars(optional($processo->exportador)->Exportador ?? 'N/D'));

        // Consignee (Destinatário)
        $consignee = $trader->addChild('Consignee');
        $consignee->addChild('Consignee_code', optional($processo->cliente)->CustomerTaxID ?? 'N/D');
        $consignee->addChild('Consignee_name', optional($processo->cliente)->CompanyName ?? 'N/D');

        // Financeiro
        $financial = $trader->addChild('Financial');
        $financial->addChild('Financial_code');
        $financial->addChild('Financial_name');

        // Declarant
        $declarant = $xml->addChild('Declarant');
        $empresa = Auth::user()->empresas->first();
        $declarant->addChild('Declarant_code', $empresa->Cedula);
        $declarant->addChild('Declarant_name', "{$empresa->Empresa} {$empresa->Endereco_completo}");
        $declarant->addChild('Declarant_representative', $empresa->Empresa);

        $reference = $declarant->addChild('Reference');
        $reference->addChild('Number', $processo->vinheta); // Número de referência do processo este campo deve ser unico e não nulo no XML do asyscuda

        // Adicionar outras seções do XML
        $this->adicionarInformacoesGeraisXML($xml, $processo);
        $this->adicionarTransporteXML($xml, $processo);
        $this->adicionarInformacoesFinanceirasXML($xml, $processo);
        $this->adicionarValuationXML($xml, $processo);
        // $this->adicionarContainerXML($xml, $processo);

        // Buscar Mercadorias Agrupadas
        $mercadoriasAgrupadas = MercadoriaAgrupada::with('mercadorias')->where('processo_id', $ProcessoID)->get();

        // Verifica se existem mercadorias antes de tentar adicionar
        if ($mercadoriasAgrupadas->isEmpty()) {
            return response()->json(['error' => 'Nenhuma mercadoria encontrada para este processo'], 400);
        }

        // Adicionar cada mercadoria como Item no XML
        foreach ($mercadoriasAgrupadas as $mercadoria) {
            // dd($mercadoriasAgrupadas);
            $this->adicionarItemXML($xml, $mercadoria, $processo);
        }

        // Adicionar lista de veículos (se aplicável)
        $xml->addChild('Vehicle_List');

        // Caminho do Arquivo XML Gerado
        $fileName = 'processo_'.$processo->NrProcesso.'_'.Carbon::now()->format('d_m_y_Hms'). '.xml';
        $filePath = storage_path("app/public/{$fileName}");

        // Salvar XML no Servidor
        $xml->asXML($filePath);

        // Retornar o XML para download
        return response()->download($filePath, $fileName, ['Content-Type' => 'application/xml']);
    }

    function adicionarIdentificacaoXML($xml, $processo)
    {
        $identification = $xml->addChild('Identification');

        // Office Segment
        $officeSegment = $identification->addChild('Office_segment');
        $officeSegment->addChild('Customs_clearance_office_code', $processo->estancia->cod_estancia ?? '');
        $officeSegment->addChild('Customs_Clearance_office_name', $processo->estancia->desc_estancia ?? '');

        // Type
        $type = $identification->addChild('Type');
        $type->addChild('Type_of_declaration', $processo->tipoProcesso->abrev ?? 'IM');
        $type->addChild('Declaration_gen_procedure_code', $processo->tipoProcesso->codigo ?? '4');
        $type->addChild('Type_of_transit_document', null);

        // Manifest Reference Number
        $identification->addChild('Manifest_reference_number', $processo->RefCliente ?? '');

        // Registration
        $registration = $identification->addChild('Registration');
        $registration->addChild('Serial_number', null);
        $registration->addChild('Number');
        $registration->addChild('Date');

        // Assessment
        $assessment = $identification->addChild('Assessment');
        $assessment->addChild('Serial_number', null);
        $assessment->addChild('Number');
        $assessment->addChild('Date');

        // Receipt
        $receipt = $identification->addChild('receipt');
        $receipt->addChild('Serial_number', null);
        $receipt->addChild('Number');
        $receipt->addChild('Date');
    }

    private function adicionarItemXML($xml, $mercadoriaP, $processo)
    {
        $item = $xml->addChild('Item');

        // Documentos Anexados
        $attachedDocs = $item->addChild('Attached_documents');
        $attachedDocs->addChild('Attached_document_code', '403');
        $attachedDocs->addChild('Attached_document_name', 'Factura (s) Comercial Definitiva / Declaração de Valores');
        $attachedDocs->addChild('Attached_document_reference', $processo->documento_referencia);
        $attachedDocs->addChild('Attached_document_from_rule', '1');
        $attachedDocs->addChild('Attached_document_date', date('m/d/y', strtotime($processo->data_documento)));

        // Pacotes
        $packages = $item->addChild('Packages');
        $packages->addChild('Number_of_packages', count($mercadoriaP->mercadorias));
        $packages->addChild('Marks1_of_packages', $mercadoriaP->Descricao);
        //$packages->addChild('Marks2_of_packages', $mercadoria->marcacao_pacotes_2);
        $packages->addChild('Kind_of_packages_code', 'PK');
        $packages->addChild('Kind_of_packages_name', 'Volumes n.e.');

        // Incoterms
        $incoTerms = $item->addChild('IncoTerms');
        $incoTerms->addChild('Code', 'CIF');
        $incoTerms->addChild('Place', $processo->Moeda.': '.$processo->cif);

        // Tarifação
        $tarification = $item->addChild('Tarification');
            $hscode = $tarification->addChild('HScode');
            $hscode->addChild('Commodity_code', $mercadoriaP->codigo_aduaneiro);
            $hscode->addChild('Precision_1', '00');
            $tarification->addChild('Extended_customs_procedure', '4100');
            $tarification->addChild('National_customs_procedure', '000');
            $tarification->addChild('Item_price', $mercadoriaP->preco_total);
            $tarification->addChild('Valuation_method_code');
            $tarification->addChild('Value_item');

        // Descrição da Mercadoria
        $descricao = $item->addChild('Goods_description');
        $descricao->addChild('Country_of_origin_code', $processo->paisOrigem->codigo);
        $descricao->addChild('Commercial_Description', $mercadoriaP->pautaAduaneira->descricao);

        // Licença
        $licenca = $item->addChild('Licence');
        $licenca->addChild('Licence_number', $mercadoriaP->numero_licenca);

        // Peso e CIF
        $valuation = $item->addChild('Valuation_item');
        $weight = $valuation->addChild('Weight_item');
        $weight->addChild('Gross_weight_item', $mercadoriaP->peso_total);
        $weight->addChild('Net_weight_item', $mercadoriaP->peso_total);
    }

    function adicionarInformacoesGeraisXML($xml, $processo)
    {
        $generalInfo = $xml->addChild('General_information');

        // Country Section
        $country = $generalInfo->addChild('Country');
        $country->addChild('Country_first_destination', $processo->paisOrigem->codigo ?? '');
        $country->addChild('Trading_country', $processo->paisOrigem->codigo ?? '');

        // Export
        $export = $country->addChild('Export');
        $export->addChild('Export_country_code', $processo->paisOrigem->codigo ?? '');
        $export->addChild('Export_country_name', $processo->paisOrigem->pais ?? '');
        $export->addChild('Export_country_region');

        // Destination
        $destination = $country->addChild('Destination');
        $destination->addChild('Destination_country_code', 'AO');
        $destination->addChild('Destination_country_name', 'Angola');
        $destination->addChild('Destination_country_region');

        $country->addChild('Country_of_origin_name', $processo->paisOrigem->pais ?? '');

        // Value Details
        $generalInfo->addChild('Value_details', $processo->fob_total ?? '0.00');

        // Additional Information
        $generalInfo->addChild('CAP');
        $generalInfo->addChild('Additional_information');
        $generalInfo->addChild('Comments_free_text');
    }

    function adicionarTransporteXML($xml, $processo)
    {
        $transport = $xml->addChild('Transport');

        // Means of Transport
        $meansOfTransport = $transport->addChild('Means_of_transport');

        // Departure/Arrival Information
        $departureArrival = $meansOfTransport->addChild('Departure_arrival_information');
        $departureArrival->addChild('Identity', $processo->registo_transporte ?? '');
        $departureArrival->addChild('Nationality', $processo->nacionalidadeNavio->codigo ?? '');

        // Border Information
        $borderInfo = $meansOfTransport->addChild('Border_information');
        $borderInfo->addChild('Identity', $processo->registo_transporte ?? '');
        $borderInfo->addChild('Nationality', $processo->nacionalidadeNavio->codigo ?? '');
        $borderInfo->addChild('Mode', $processo->TipoTransporte ?? '1');

        // Inland Mode of Transport
        $meansOfTransport->addChild('Inland_mode_of_transport');

        // Container Flag
        $transport->addChild('Container_flag', 'true');

        // Delivery Terms
        $deliveryTerms = $transport->addChild('Delivery_terms');
        $deliveryTerms->addChild('Code','CIF');
        $deliveryTerms->addChild('Place', $processo->Moeda.': '.$processo->cif ?? '');
        $deliveryTerms->addChild('Situation');

        // Border Office -> Posto de Fronteira
        $borderOffice = $transport->addChild('Border_office');
        $borderOffice->addChild('Code', $processo->estancia->cod_estancia ?? '');
        $borderOffice->addChild('Name', $processo->estancia->desc_estancia ?? '');

        // Place of Loading
        $placeOfLoading = $transport->addChild('Place_of_loading'); // Place of Loading -> Porto de Desembarque
        $placeOfLoading->addChild('Code', $processo->portoDesembarque->pais->codigo ?? 'AO'.$processo->portoDesembarque->sigla  ?? 'LDA');
        $placeOfLoading->addChild('Name', $processo->portoDesembarque->porto ?? 'Luanda');
        $placeOfLoading->addChild('Country');

        // Location of Goods -> Local de Armazenamento
        $transport->addChild('Location_of_goods', $processo->localizacaoMercadoria->codigo);
    }

    function adicionarInformacoesFinanceirasXML($xml, $processo)
    {
        $financial = $xml->addChild('Financial');

        // Financial Transaction
        $financialTransaction = $financial->addChild('Financial_transaction');
        $financialTransaction->addChild('code1', $processo->transacao_codigo1 ?? '1');
        $financialTransaction->addChild('code2', $processo->transacao_codigo2 ?? '2');

        // Bank Information
        $bank = $financial->addChild('Bank');
        $bank->addChild('Code');
        $bank->addChild('Name');
        $bank->addChild('Branch');
        $bank->addChild('Reference');

        // Payment Terms
        $terms = $financial->addChild('Terms');
        $terms->addChild('Code');
        $terms->addChild('Description');

        // Invoice Total
        $financial->addChild('Total_invoice');

        // Deferred Payment Reference
        $financial->addChild('Deffered_payment_reference');

        // Mode of Payment
        $financial->addChild('Mode_of_payment', 'PRONTO PAGAMENTO');

        // Amounts Section
        $amounts = $financial->addChild('Amounts');
        $amounts->addChild('Total_manual_taxes');
        $amounts->addChild('Global_taxes', '0.00');
        $amounts->addChild('Totals_taxes', '0.00');

        // Guarantee Information
        $guarantee = $financial->addChild('Guarantee');
        $guarantee->addChild('Name');
        $guarantee->addChild('Amount', '0.0');
        $guarantee->addChild('Date');

        // Excluded Country
        $excludedCountry = $guarantee->addChild('Excluded_country');
        $excludedCountry->addChild('Code');
        $excludedCountry->addChild('Name');
    }

    function adicionarTransitXML($xml, $processo)
    {
        $transit = $xml->addChild('Transit');

        // Principal
        $principal = $transit->addChild('Principal');
        $principal->addChild('Code');
        $principal->addChild('Name');
        $principal->addChild('Representative');

        // Signature
        $signature = $transit->addChild('Signature');
        $signature->addChild('Place');
        $signature->addChild('Date');

        // Destination
        $destination = $transit->addChild('Destination');
        $destination->addChild('Office');
        $destination->addChild('Country');

        // Seals
        $seals = $transit->addChild('Seals');
        $seals->addChild('Number');
        $seals->addChild('Identity');

        $transit->addChild('Result_of_control');
        $transit->addChild('Time_limit');
        $transit->addChild('Officer_name');
    }

    function adicionarValuationXML($xml, $processo)
    {
        $frete_nacional = $processo->frete*$processo->Cambio;
        $seguro_nacional = $processo->seguro*$processo->Cambio;
        $totalCIF = $processo->ValorAduaneiro + $frete_nacional + $seguro_nacional; // O valor deve estar em anotação cientifica

        $valuation = $xml->addChild('Valuation');

        // Modo de Cálculo
        $valuation->addChild('Calculation_working_mode', '0');

        // Peso Total
        $weight = $valuation->addChild('Weight');
        $weight->addChild('Gross_weight', $processo->peso_bruto ?? '');

        // Custos Totais
        //$valuation->addChild('Total_cost', $processo->custo_total ?? '0.00');
        $valuation->addChild('Total_CIF', $processo->totalCIF ?? '0.00');

        // Fatura
        $gsInvoice = $valuation->addChild('Gs_Invoice');
        $gsInvoice->addChild('Amount_national_currency', $processo->ValorAduaneiro ?? '0.00');
        $gsInvoice->addChild('Amount_foreign_currency', $processo->fob_total ?? '20350');
        $gsInvoice->addChild('Currency_code', $processo->Moeda);
        $gsInvoice->addChild('Currency_name', 'Nao existe Divisa');
        $gsInvoice->addChild('Currency_rate', $processo->Cambio ?? '0.00');

        // Frete Externo
        $gsExternalFreight = $valuation->addChild('Gs_external_freight');
        $gsExternalFreight->addChild('Amount_national_currency', $frete_nacional ?? '0.00');
        $gsExternalFreight->addChild('Amount_foreign_currency', $processo->frete ?? '0.00');
        $gsExternalFreight->addChild('Currency_code', 'EUR');
        $gsExternalFreight->addChild('Currency_name', 'Nao existe Divisa');
        $gsExternalFreight->addChild('Currency_rate', $processo->Cambio ?? '0.00');

        // Frete Interno
        $gsInternalFreight = $valuation->addChild('Gs_internal_freight');
        $gsInternalFreight->addChild('Amount_national_currency', '0.00');
        $gsInternalFreight->addChild('Amount_foreign_currency', '0');
        $gsInternalFreight->addChild('Currency_code');
        $gsInternalFreight->addChild('Currency_name', 'Nao existe Divisa');
        $gsInternalFreight->addChild('Currency_rate', '0');

        // Seguro
        $gsInsurance = $valuation->addChild('Gs_insurance');
        $gsInsurance->addChild('Amount_national_currency', $seguro_nacional ?? '0.00');
        $gsInsurance->addChild('Amount_foreign_currency', $processo->seguro ?? '0.00');
        $gsInsurance->addChild('Currency_code', 'EUR');
        $gsInsurance->addChild('Currency_name', 'Nao existe Divisa');
        $gsInsurance->addChild('Currency_rate', $processo->Cambio ?? '0.00');

        // Outros Custos
        $gsOtherCost = $valuation->addChild('Gs_other_cost');
        $gsOtherCost->addChild('Amount_national_currency', '0.00');
        $gsOtherCost->addChild('Amount_foreign_currency', '0');
        $gsOtherCost->addChild('Currency_code');
        $gsOtherCost->addChild('Currency_name', 'Nao existe Divisa');
        $gsOtherCost->addChild('Currency_rate', '0');

        // Dedução
        $gsDeduction = $valuation->addChild('Gs_deduction');
        $gsDeduction->addChild('Amount_national_currency', '0.00');
        $gsDeduction->addChild('Amount_foreign_currency', '0');
        $gsDeduction->addChild('Currency_code');
        $gsDeduction->addChild('Currency_name', 'Nao existe Divisa');
        $gsDeduction->addChild('Currency_rate', '0');

        // Totais
        $total = $valuation->addChild('Total');
        $total->addChild('Total_invoice', $processo->fob_total ?? '0.00');
        $total->addChild('Total_weight', $processo->peso_total ?? '0.00');
    }

    function adicionarContainerXML($xml, $processo)
    {
        // Recupera os containers associados ao processo
        $containers = DB::table('containers')->where('processo_id', $processo->id)->get();

        foreach ($containers as $index => $container) {
            $containerXml = $xml->addChild('Container');
            
            $containerXml->addChild('Item_Number', $index + 1);
            $containerXml->addChild('Container_identity', $container->identidade ?? 'Desconhecido');
            $containerXml->addChild('Container_type', $container->tipo ?? 'Desconhecido');
            $containerXml->addChild('Empty_full_indicator', $container->indicador_vazio_cheio ?? 'FCL');
            $containerXml->addChild('Gross_weight', $container->peso_bruto ?? '');
            $containerXml->addChild('Goods_description', $container->descricao_mercadoria ?? 'OUTRAS');
            $containerXml->addChild('Packages_type', $container->tipo_pacote ?? 'PK');
            $containerXml->addChild('Packages_number', $container->numero_pacotes ?? '0');
            $containerXml->addChild('Packages_weight', $container->peso_pacotes ?? '0.00');
        }
    }

}
