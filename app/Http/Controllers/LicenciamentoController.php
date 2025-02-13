<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\LicenciamentoRequest;
use App\Models\Customer;
use App\Models\EmpresaUser;
use App\Models\Estancia;
use App\Models\Exportador;
use App\Models\Importacao;
use App\Models\Licenciamento;
use App\Models\LicenciamentoRascunho;
use App\Models\Mercadoria;
use App\Models\MercadoriaAgrupada;
use App\Models\Pais;
use App\Models\PautaAduaneira;
use App\Models\Porto;
use App\Models\Processo;
use App\Models\RegiaoAduaneira;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use App\Exports\LicenciamentosExport;
use Maatwebsite\Excel\Facades\Excel;

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
        return view('processos.licenciamento', 
        compact('clientes', 
                'estancias', 
                'regioes', 
                'exportador', 
                'paises', 
                'empresa', 
                'portos', 
                'ibans', 
                'pautaAduaneira'
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
        try {
            // Verificar se o licenciamento tem faturas ou outros registros relacionados
            if ($licenciamento->procLicenFaturas->isNotEmpty()) {
                return redirect()->route('licenciamentos.index')
                                ->with('error', 'Não é possível excluir o licenciamento. Existem faturas associadas.');
            }

            // Excluir o licenciamento
            $licenciamento->delete();

            return redirect()->route('licenciamentos.index')
                            ->with('success', 'Licenciamento excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->route('licenciamentos.index')
                            ->with('error', 'Erro ao excluir o licenciamento. Tente novamente.');
        }
    }


    public function GerarTxT($Idlice)
    {
        // Buscando o processo pelo ID
        $licenciamento = Licenciamento::findOrFail($Idlice);

        // Verificar se os campos importantes estão preenchidos
        if (is_null($licenciamento->frete) || is_null($licenciamento->seguro) || $licenciamento->frete === 0 || $licenciamento->seguro === 0) {
            // Redirecionar de volta com uma mensagem de erro
            return redirect()->back()->withErrors(['error' => 'Os campos Frete e Seguro precisam estar preenchidos e diferentes de zero antes de gerar o licenciamento.']);
        }

        $mercadoriaAgrupada = MercadoriaAgrupada::where('licenciamento_id', $licenciamento->id)->get();

        $porto = DB::table('portos')->join('paises', 'portos.pais_id', '=', 'paises.id')
        ->where('portos.porto', $licenciamento->porto_origem)
        ->select('portos.sigla', 'paises.codigo as codigo_pais')->first();

        $FOB = $licenciamento->fob_total;
        $Frete = $licenciamento->frete;
        $Seguro = $licenciamento->seguro;

        // Linha 0 - Cabeçalho do processo
        $linha0 = "0|" . count($mercadoriaAgrupada) . "|{$licenciamento->estancia_id}|{$licenciamento->cliente->CompanyName}|{$licenciamento->empresa->Empresa}|{$licenciamento->empresa->Cedula}|{$licenciamento->empresa->Email}|{$licenciamento->referencia_cliente}|||||||||||||||||||||||||||||";
        
        // Linha 1 - Informações do exportador e transporte
        $linha1 = "1|{$licenciamento->exportador->ExportadorTaxID}|{$licenciamento->exportador->Exportador}|{$licenciamento->cliente->CustomerTaxID}||{$licenciamento->empresa->Cedula}|{$licenciamento->tipo_transporte}|{$licenciamento->registo_transporte}|{$licenciamento->pais->codigo}|{$licenciamento->manifesto}|{$licenciamento->factura_proforma}|//|{$licenciamento->porto_entrada}|{$licenciamento->tipo_declaracao}|{$licenciamento->estancia_id}|" . count($mercadoriaAgrupada) . "|{$licenciamento->peso_bruto}||||{$licenciamento->metodo_avaliacao}|{$licenciamento->forma_pagamento}|{$licenciamento->codigo_banco}|{$licenciamento->codigo_volume}|{$licenciamento->qntd_volume}|{$licenciamento->descricao}||||{$porto->codigo_pais}{$porto->sigla}||{$porto->codigo_pais}|AO||||";
        
        // Linha 2 - Adições de mercadorias
        $adicoes = [];
        foreach ($mercadoriaAgrupada as $key => $adicao) {

            $pautaAduaneira = PautaAduaneira::where(DB::raw("REPLACE(codigo, '.', '')"), $adicao->codigo_aduaneiro)->first();

            $ordem = $key + 1;
            
            // Calculando Frete e Seguro proporcionais
            $frete_seguro = Mercadoria::calcularFreteMercadoria($adicao->preco_total, $FOB, $Frete) 
                        + Mercadoria::calcularSeguroMercadoria($adicao->preco_total, $FOB, $Seguro);
            
            $CIF = $frete_seguro + $adicao->preco_total;
            
            if ($adicao->peso_total == 0) {
                $peso = $licenciamento->peso_bruto / count($mercadoriaAgrupada);
            }else{
                $peso = $adicao->peso_total;
            }
            // Criando a linha de adição
            $adicoes[] = sprintf(
                "2|%d|||||%s|%d||%s|%s|%s|%s|%s|%s|||%s|||||||||||||||||||",
                $ordem,
                $adicao->codigo_aduaneiro ?? 'N/A',             // Código aduaneiro ou padrão 'N/A'
                $adicao->quantidade_total ?? 0,                 // Quantidade total ou padrão '0'
                $porto->codigo_pais ?? 'N/A',                   // País de origem ou padrão 'N/A'
                $peso ?? '0.00',                                // Peso ou padrão '0.00'
                $licenciamento->moeda ?? 'N/A',                 // Moeda ou padrão 'N/A'
                $adicao->preco_total ?? '0.00',                 // Preço total ou padrão '0.00'
                $frete_seguro ?? '0.00',                        // Frete e seguro proporcionais ou padrão '0.00'
                $CIF ?? '0.00',                                 // CIF ou padrão '0.00'
                $pautaAduaneira->uq ?? 'N/A'                    // Unidade de quantidade ou padrão 'N/A'
            );
            
        }

        // Montando o conteúdo completo
        $conteudo = $linha0 . "\n" . $linha1 . "\n" . implode("\n", $adicoes);

        // Nome do arquivo
        $nomeArquivo = 'licenciamento_' . $licenciamento->codigo_licenciamento . '.txt';

        $licenciamento->txt_gerado = 1;
        $licenciamento->save();

        // Criando e retornando o arquivo .txt para download
        return response($conteudo)->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="'.$nomeArquivo.'"');
    }

    public function ConstituirProcesso(Request $request, $idLicenciamento)
    {
        // Busca o licenciamento pelo ID
        $licenca = Licenciamento::findOrFail($idLicenciamento);

        // Inicia a transação
        DB::beginTransaction();

        try {
            // Cria o processo baseado no licenciamento
            $processo = Processo::create([
                'ContaDespacho' => $licenca->referencia_cliente,
                'RefCliente' => $licenca->referencia_cliente,
                'Descricao' => $licenca->descricao,
                'DataAbertura' => now(),
                'TipoProcesso' => $licenca->tipo_declaracao,
                'Situacao' => 'Aberto',
                'customer_id' => $licenca->cliente_id,
                'user_id' => Auth::user()->id,
                'empresa_id' => $licenca->empresa_id,
                'exportador_id' => $licenca->exportador_id,
                'estancia_id' => $licenca->estancia_id,
            ]);

            // Cria a importação associada ao processo
            $importacao = Importacao::create([
                'processo_id' => $processo->id,
                'FOB' => $licenca->fob_total,
                'Freight' => $licenca->frete,
                'Insurance' => $licenca->seguro,
                'Fk_pais_origem' => $licenca->pais_origem, // Supondo que seja um ID em uma tabela de países
                'PortoOrigem' => $licenca->porto_origem,
                'TipoTransporte' => $licenca->tipo_transporte,
                'NomeTransporte' => $licenca->registo_transporte,
                'DataChegada' => $licenca->data_entrada,
                'Moeda' => $licenca->moeda,
                'Cambio' => 1.0, // Ajuste conforme a lógica para câmbio
                'ValorTotal' => $licenca->cif,
                'ValorAduaneiro' => $licenca->cif + $licenca->frete + $licenca->seguro, // Ajuste se necessário
            ]);

            // Atualiza as mercadorias associadas ao licenciamento para vinculá-las ao novo processo
            Mercadoria::where('licenciamento_id', $idLicenciamento)
                ->update(['Fk_Importacao	' => $importacao->id]);

            // Confirma a transação
            DB::commit();

            // Redireciona para edição do processo
            return redirect()->route('processos.edit', $processo->id)
                ->with(['success' => true, 'message' => 'Processo constituído com sucesso pelo Licenciamento ' . $licenca->codigo_licenciamento . '.']);
            }catch (QueryException $th) {
                DB::rollBack();
                return DatabaseErrorHandler::handle($th, $request);
            }
            
    }

    public function exportCsv()
    {
        $licenciamentos = Licenciamento::where('empresa_id', Auth::user()->empresas->first()->id)->get();

        // Definindo o nome do arquivo
        $fileName = 'licenciamentos_' . now()->format('Ymd_His') . '.csv';

        // Cabeçalhos para o arquivo CSV
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        // Abrir o arquivo para escrita
        $handle = fopen('php://output', 'w');

        // Escrever a linha de cabeçalhos no CSV
        fputcsv($handle, [
            'Cliente', 'Descrição', 'Peso Bruto','Unidade', 'Origem', 'Estado', 'CIF','Moeda', 'Factura'
        ]);

        // Escrever os dados de cada licenciamentos
        foreach ($licenciamentos as $licenciamento) {
            fputcsv($handle, [
                $licenciamento->cliente->CompanyName,
                $licenciamento->descricao,
                $licenciamento->peso_bruto,
                $licenciamento->peso_bruto < 1000 ? ' Kg' : ' Ton',
                $licenciamento->porto_origem,
                ucfirst($licenciamento->estado_licenciamento),
                number_format($licenciamento->cif, 2, ',', '.'),
                $licenciamento->moeda,
                $licenciamento->procLicenFaturas->isNotEmpty() ? $licenciamento->Nr_factura : 'Sem Factura'
            ]);
        }

        // Fechar o arquivo
        fclose($handle);

        // Retornar a resposta com os cabeçalhos apropriados
        return Response::make('', 200, $headers);
    }

    public function exportExcel()
    {
        return Excel::download(new LicenciamentosExport, 'licenciamentos.xlsx');
    }

    public function import(Request $request)
    {
        // Validar o arquivo
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls'
        ]);

        // Processar o arquivo CSV ou Excel
        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            if ($extension == 'csv') {
                // Processamento para CSV
                $data = array_map('str_getcsv', file($file));
            } else {
                // Processamento para Excel usando Laravel Excel
                $data = Excel::toArray([], $file);
            }

            // Aqui você pode fazer a inserção dos dados no banco de dados

            return back()->with('success', 'Ficheiro importado com sucesso!');
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao importar o ficheiro: ' . $e->getMessage());
        }
    }

}
