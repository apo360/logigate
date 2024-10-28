<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\LicenciamentoRequest;
use App\Models\Customer;
use App\Models\EmpresaUser;
use App\Models\Estancia;
use App\Models\Exportador;
use App\Models\Licenciamento;
use App\Models\LicenciamentoRascunho;
use App\Models\Mercadoria;
use App\Models\MercadoriaAgrupada;
use App\Models\Pais;
use App\Models\PautaAduaneira;
use App\Models\Porto;
use App\Models\RegiaoAduaneira;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LicenciamentoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Filtrar por empresa se fornecido
        $empresaId = Auth::user()->empresas->first()->id;
        
        // Inicializando a consulta de licenciamentos
        $query = Licenciamento::query();

        // Aplicando filtro se houver empresa
        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }

        // Paginar os resultados para exibir 10 por página
        $licenciamentos = $query->with(['empresa', 'cliente', 'exportador'])
                                ->orderBy('created_at', 'desc') // Ordenar por data de criação, mais recente primeiro
                                ->paginate(10);

        // Retornar a view com os licenciamentos paginados
        return view('processos.licenciamento_index', compact('licenciamentos'));
    }

     
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Customer::where('empresa_id', Auth::user()->empresas->first()->id)->get();
        $exportador = Exportador::where('empresa_id', Auth::user()->empresas->first()->id)->get();
        $estancias = Estancia::all();
        $regioes = RegiaoAduaneira::all();
        $paises = Pais::all();
        $portos = Porto::all();
        $ibans = IbanController::getBankDetails();
        $pautaAduaneira = PautaAduaneira::all();
        $empresa = EmpresaUser::where('empresa_id', Auth::user()->empresas->first()->id)->get();

        // chamar a stored procedure
        $newCustomerCode = Customer::generateNewCode();
        $newExportadorCode = Exportador::generateNewCode();
        return view('processos.licenciamento', 
        compact('clientes', 
                'estancias', 
                'regioes', 
                'exportador', 
                'paises', 
                'empresa', 
                'portos', 
                'ibans', 
                'pautaAduaneira',
                'newCustomerCode', 
                'newExportadorCode'
            ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(LicenciamentoRequest $request)
    {
        // Todos os dados já foram validados pelo StoreLicenciamentoRequest
        try {
            DB::beginTransaction();
            // Obtém o usuário autenticado
            $user = Auth::user();

            $licenciamento_request = $request->validated();

            $licenciamento_request['empresa_id'] = $user->empresas->first()->id;
            $licenciamento_request['peso_bruto'] = 0.00;
            $licenciamento_request['adicoes'] = 0;

            // Criar licenciamento (código será gerado automaticamente pelo Model)
            $licenciamento = Licenciamento::create($licenciamento_request);

            DB::commit();

            // Redirecionar após a criação com uma mensagem de sucesso
            return redirect()->route('mercadorias.create', ['licenciamento_id' => $licenciamento->id])->with('success', 'Licenciamento criado com sucesso!');

        } 
        catch (QueryException $th) {
            return DatabaseErrorHandler::handle($th, $request);
        }
        
    }

    public function storeDraft(Request $request){

        try {
            DB::beginTransaction();

            // Obtém o usuário autenticado
            $user = Auth::user();

            $licenciamento_rascunho = $request->all();

            $licenciamento_rascunho['empresa_id'] = $user->empresas->first()->id;
            
            $rascunho = LicenciamentoRascunho::create($licenciamento_rascunho);

            DB::commit();

            return redirect()->back()->with('warning', 'Licenciamento Salvo como Rascunho');
        } catch (QueryException $th) {
            return DatabaseErrorHandler::handle($th, $request);
        }
    }

    /**
     * Display the specified resource.
     */

    public function show(Licenciamento $licenciamento)
    {
        // Buscar o licenciamento pelo ID
        $licenciamento = Licenciamento::with('mercadorias')->findOrFail($licenciamento->id);

        // Retornar a view com os dados do licenciamento
        return view('processos.licenciamento_show', compact('licenciamento'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Licenciamento $licenciamento)
    {
        // Verifica se o licenciamento pode ser editado
        if (!$licenciamento->podeSerEditado()) {
            return redirect()->route('licenciamento.index')->with('error', 'Este licenciamento não pode ser editado.');
        }

        $estancias = Estancia::all();
        $regioes = RegiaoAduaneira::all();
        $paises = Pais::all();
        $portos = Porto::all();
        $bancos = IbanController::getBankDetails();

        // Continue com o processo de edição
        return view('processos.licenciamento_edit', compact('licenciamento', 'bancos','portos', 'paises', 'regioes', 'estancias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(LicenciamentoRequest $request, Licenciamento $licenciamento)
    {

        $user = Auth::user();
        // Verifica se o licenciamento pode ser editado
        if (!$licenciamento->podeSerEditado()) {
            return redirect()->route('licenciamento.index')->with('error', 'Este licenciamento não pode ser atualizado.');
        }

        try {
            DB::beginTransaction();

            $licenciamento_request = $request->validated();

            $licenciamento_request['empresa_id'] = $user->empresas->first()->id;

            // Atualizar o licenciamento
            $licenciamento->update($licenciamento_request);

            DB::commit();

            return redirect()->route('licenciamentos.edit', $licenciamento->id)->with('success', 'Licenciamento atualizado com sucesso!');

        }catch (QueryException $th) {
            return DatabaseErrorHandler::handle($th, $request);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Licenciamento $licenciamento)
    {
        //
    }

    public function GerarTxT($Idlice)
    {
        // Buscando o processo pelo ID
        $licenciamento = Licenciamento::findOrFail($Idlice);

        // Verificando se os campos Frete e Seguro estão preenchidos
        if (empty($licenciamento->frete) || empty($licenciamento->seguro)) {
            // Redirecionar de volta com uma mensagem de erro
            return redirect()->back()->with('error', 'Os campos Frete e Seguro precisam ser preenchidos antes de gerar o licenciamento.');
        }

        $mercadoriaAgrupada = MercadoriaAgrupada::where('licenciamento_id', $licenciamento->id)->get();

        $FOB = $licenciamento->fob_total;
        $Frete = $licenciamento->frete;
        $Seguro = $licenciamento->seguro;

        // Linha 0 - Cabeçalho do processo
        $linha0 = "0|" . count($mercadoriaAgrupada) . "|{$licenciamento->estancia_id}|{$licenciamento->cliente->CompanyName}|{$licenciamento->empresa->Empresa}|{$licenciamento->empresa->Cedula}|{$licenciamento->empresa->Email}|{$licenciamento->referencia_cliente}|||||||||||||||||||||||||||||";
        
        // Linha 1 - Informações do exportador e transporte
        $linha1 = "1|{$licenciamento->exportador->ExportadorTaxID}|{$licenciamento->exportador->Exportador}|{$licenciamento->cliente->CustomerTaxID}||{$licenciamento->empresa->Cedula}|{$licenciamento->tipo_transporte}|{$licenciamento->registo_transporte}|{$licenciamento->nacionalidade_transporte}|{$licenciamento->manifesto}|{$licenciamento->factura_proforma}|//|{$licenciamento->porto_entrada}|{$licenciamento->tipo_declaracao}|{$licenciamento->estancia_id}|" . count($mercadoriaAgrupada) . "|{$licenciamento->peso_bruto}||||{$licenciamento->metodo_avaliacao}|{$licenciamento->forma_pagamento}|{$licenciamento->codigo_banco}|{$licenciamento->codigo_volume}|{$licenciamento->qntd_volume}|{$licenciamento->descricao}||||{$licenciamento->pais_origem}{$licenciamento->porto_origem}|{$licenciamento->pais_origem}|AO||||";
        
        // Linha 2 - Adições de mercadorias
        $adicoes = [];
        foreach ($mercadoriaAgrupada as $key => $adicao) {
            $ordem = $key + 1;
            
            // Calculando Frete e Seguro proporcionais
            $frete_seguro = Mercadoria::calcularFreteMercadoria($adicao->preco_total, $FOB, $Frete) 
                        + Mercadoria::calcularSeguroMercadoria($adicao->preco_total, $FOB, $Seguro);
            
            $CIF = $frete_seguro + $adicao->preco_total;
            
            // Criando a linha de adição
            $adicoes[] = "2|{$ordem}|||||{$adicao->codigo_aduaneiro}|{$adicao->quantidade_total}||{$licenciamento->pais_origem}|{$adicao->peso_total}|{$licenciamento->moeda}|{$adicao->preco_total}|{$frete_seguro}|{$CIF}|||Kg|||||||||||||||||||";
        }

        // Montando o conteúdo completo
        $conteudo = $linha0 . "\n" . $linha1 . "\n" . implode("\n", $adicoes);

        // Nome do arquivo
        $nomeArquivo = 'licenciamento_' . $licenciamento->codigo_licenciamento . '.txt';

        $licenciamento->txt_gerado = 1;
        $licenciamento->save();

        // Criando e retornando o arquivo .txt para download
        return response($conteudo)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="'.$nomeArquivo.'"');
    }
}
