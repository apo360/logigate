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
use App\Models\ProcLicenFactura;
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
        $clientes = $this->empresa->customers()->get();
        $exportador = $this->empresa->exportadors()->get();
        $estancias = Estancia::all();
        $regioes = RegiaoAduaneira::all();
        $paises = Pais::all();
        $portos = Porto::all();
        $ibans = IbanController::getBankDetails();
        $pautaAduaneira = PautaAduaneira::all();
        $empresa = EmpresaUser::where('empresa_id', $this->empresa->id)->get();

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

            // Pegar o ID do país a partir da Sigla porto de origem
            $porto = Porto::where('sigla', $licenciamento_request['porto_origem'])->first();
            if ($porto) {
                $licenciamento_request['pais_origem'] = $porto->pais_id;
            } else {
                $licenciamento_request['pais_origem'] = null;
            }

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

        try {
            DB::beginTransaction();

            $licenciamento_request = $request->validated();

            $licenciamento_request['empresa_id'] = $user->empresas->first()->id;
            
            // Pegar o ID do país a partir da Sigla porto de origem
            $porto = Porto::where('sigla', $licenciamento_request['porto_origem'])->first();
            if ($porto) {
                $licenciamento_request['pais_origem'] = $porto->pais_id;
            } else {
                $licenciamento_request['pais_origem'] = null;
            }

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
            // Verificar se o licenciamento tem facturas, Processos ou outros registros relacionados
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
    /**
     * Gerar arquivo TXT para o licenciamento.
     */
    // ...existing code...
    public function GerarTxT($Idlice)
    {
        try {
            // Buscando o processo pelo ID
            $licenciamento = Licenciamento::findOrFail($Idlice);
    
            // Verificar se os campos importantes estão preenchidos
            if (is_null($licenciamento->frete) || is_null($licenciamento->seguro) || $licenciamento->frete === 0 || $licenciamento->seguro === 0) {
                return redirect()->back()->withErrors(['error' => 'Os campos Frete e Seguro precisam estar preenchidos e diferentes de zero antes de gerar o licenciamento.']);
            }
    
            $mercadoriaAgrupada = MercadoriaAgrupada::where('licenciamento_id', $licenciamento->id)->get();
    
            if ($mercadoriaAgrupada->isEmpty()) {
                return redirect()->back()->withErrors(['error' => 'Nenhuma mercadoria agrupada encontrada para este licenciamento.']);
            }
    
            $porto = Porto::where('sigla', $licenciamento->porto_origem)->first();
            $pais_ = Pais::findOrFail($porto->pais_id);
            if (!$porto || !$porto->pais) {
                return redirect()->back()->withErrors(['error' => 'Porto de origem não encontrado ou sem país associado.']);
            }
    
            $FOB = $licenciamento->fob_total;
            $Frete = $licenciamento->frete;
            $Seguro = $licenciamento->seguro;
    
            // Linha 0 - Cabeçalho do processo
            $linha0 = "0|" . count($mercadoriaAgrupada) . "|{$licenciamento->estancia_id}|{$licenciamento->cliente->CompanyName}|{$licenciamento->empresa->Empresa}|{$licenciamento->empresa->Cedula}|{$licenciamento->empresa->Email}|{$licenciamento->referencia_cliente}|||||||||||||||||||||||||||||";
    
            // Linha 1 - Informações do exportador e transporte
            $linha1 = "1|{$licenciamento->exportador->ExportadorTaxID}|{$licenciamento->exportador->Exportador}|{$licenciamento->cliente->CustomerTaxID}||{$licenciamento->empresa->Cedula}|{$licenciamento->tipo_transporte}|{$licenciamento->registo_transporte}|{$licenciamento->pais->codigo}|{$licenciamento->manifesto}|{$licenciamento->factura_proforma}|//|{$licenciamento->porto_entrada}|{$licenciamento->tipo_declaracao}|{$licenciamento->estancia_id}|" . count($mercadoriaAgrupada) . "|{$licenciamento->peso_bruto}||||{$licenciamento->metodo_avaliacao}|{$licenciamento->forma_pagamento}|{$licenciamento->codigo_banco}|{$licenciamento->codigo_volume}|{$licenciamento->qntd_volume}|{$licenciamento->descricao}||||{$pais_->codigo}{$porto->sigla}||{$pais_->codigo}|AO||||";
    
            // Linha 2 - Adições de mercadorias
            $adicoes = [];
            foreach ($mercadoriaAgrupada as $key => $adicao) {
                $pautaAduaneira = PautaAduaneira::where(DB::raw("REPLACE(codigo, '.', '')"), $adicao->codigo_aduaneiro)->first();
    
                $ordem = $key + 1;
    
                // Calculando Frete e Seguro proporcionais
                $frete_seguro = 0;
                $CIF = 0;
                try {
                    $frete_seguro = Mercadoria::calcularFreteMercadoria($adicao->preco_total, $FOB, $Frete)
                        + Mercadoria::calcularSeguroMercadoria($adicao->preco_total, $FOB, $Seguro);
                    $CIF = $frete_seguro + $adicao->preco_total;
                } catch (\Throwable $e) {
                    return redirect()->back()->withErrors(['error' => 'Erro ao calcular frete/seguro: ' . $e->getMessage()]);
                }
    
                if ($adicao->peso_total == 0) {
                    $peso = $licenciamento->peso_bruto / count($mercadoriaAgrupada);
                } else {
                    $peso = $adicao->peso_total;
                }
    
                $adicoes[] = sprintf(
                    "2|%d|||||%s|%d||%s|%s|%s|%s|%s|%s|||%s|||||||||||||||||||",
                    $ordem,
                    $adicao->codigo_aduaneiro ?? 'N/A',
                    $adicao->quantidade_total ?? 0,
                    $pais_->codigo ?? 'N/A',
                    $peso ?? '0.00',
                    $licenciamento->moeda ?? 'N/A',
                    $adicao->preco_total ?? '0.00',
                    $frete_seguro ?? '0.00',
                    $CIF ?? '0.00',
                    $pautaAduaneira->uq ?? 'N/A'
                );
            }
    
            $conteudo = $linha0 . "\n" . $linha1 . "\n" . implode("\n", $adicoes);
    
            $nomeArquivo = 'licenciamento_' . $licenciamento->codigo_licenciamento . '.txt';
    
            $licenciamento->txt_gerado = 1;
            $licenciamento->save();
    
            return response($conteudo)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="'.$nomeArquivo.'"');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->withErrors(['error' => 'Licenciamento não encontrado.']);
        } catch (\Throwable $e) {
            return redirect()->back()->withErrors(['error' => 'Erro inesperado ao gerar o arquivo: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Constituir um processo a partir de um licenciamento.
     */
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
                'estancia_id' => $licenca->estancia_id,
                'Descricao' => $licenca->descricao,
                'DataAbertura' => now(),
                'TipoProcesso' => $licenca->tipo_declaracao,
                'Estado' => 'Aberto',
                'customer_id' => $licenca->cliente_id,
                'user_id' => Auth::user()->id,
                'empresa_id' => $licenca->empresa_id,
                'exportador_id' => $licenca->exportador_id,
                'forma_pagamento' => $licenca->forma_pagamento,
                'fob_total' => $licenca->fob_total,
                'frete' => $licenca->frete,
                'seguro' => $licenca->seguro,
                'codigo_banco' => $licenca->codigo_banco,
                'peso_bruto' => $licenca->peso_bruto,
                'TipoTransporte' => $licenca->tipo_transporte,
                'registo_transporte' => $licenca->registo_transporte,
                'nacionalidade_transporte' => $licenca->nacionalidade_transporte,
                'DataChegada' => $licenca->data_entrada,
                'Moeda' => $licenca->moeda,
                'Cambio' => 1.0, // Ajuste conforme a lógica para câmbio
                'ValorTotal' => $licenca->cif,
                'cif' => $licenca->cif,
                'ValorAduaneiro' => $licenca->cif + $licenca->frete + $licenca->seguro, // Ajuste se necessário
            ]);

            // Marca o licenciamento como utilizado
            ProcLicenFactura::updateOrCreate(
                ['licenciamento_id' => $licenca->id],
                ['processo_id' => $processo->id]
            );
            // Atualiza as mercadorias associadas ao licenciamento para vinculá-las ao novo processo
            Mercadoria::where('licenciamento_id', $idLicenciamento)->update(['Fk_Importacao' => $processo->id]);

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
            'file' => 'required|file|mimes:csv,xlsx,xls,txt|max:5120', // Limite de 5MB
        ]);

        // verificar se o arquivo é txt
        $fileMime = $request->file('file')->getClientMimeType();
        if ($fileMime == 'text/plain') {
            // Chama a Função para lidar com arquivos TXT
            $this->handleTxtImport($request);
            
            //return back()->with('error', 'O formato TXT não é suportado para importação. Por favor, utilize CSV ou Excel.');
        }
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

    private function handleTxtImport(Request $request)
    {
        // Lógica para validar e processar o arquivo TXT

        // Lógica para processar o arquivo TXT
        // Exemplo: Ler o arquivo linha por linha e processar os dados
        try {
            DB::beginTransaction();
            $file = $request->file('file');
            $lines = file($file);

            $licenciamento = null;
            $adicoes = [];
            $empresaLogada = Auth::user()->empresas->first();

            $linha0 = null;
            $linha1 = null;

            // Primeiro, capturar as linhas 0 e 1 para validação
            foreach ($lines as $line) {
                $fields = explode('|', trim($line));
                if ($fields[0] == '0') {
                    $linha0 = $fields;
                }
                if ($fields[0] == '1') {
                    $linha1 = $fields;
                }
                if ($linha0 && $linha1) break;
            }

             // Validação da empresa (nome e cedula/nif)
            $nomeEmpresaArquivo = $linha0[4] ?? null;
            $cedulaEmpresaArquivo = $linha0[5] ?? null;
            if (
                !$empresaLogada ||
                $empresaLogada->Empresa !== $nomeEmpresaArquivo ||
                $empresaLogada->Cedula !== $cedulaEmpresaArquivo
            ) {
                return back()->with('error', 'O ficheiro não pertence à empresa logada. Importação cancelada.');
            }

            // Validação do cliente
            $nomeClienteArquivo = $linha0[3] ?? null;
            $customerTaxIdArquivo = $linha1[3] ?? null;
            $cliente = \App\Models\Customer::where('CompanyName', $nomeClienteArquivo)
                ->where('CustomerTaxID', $customerTaxIdArquivo)
                ->where('empresa_id', $empresaLogada->id)
                ->first();

            if (!$cliente) {
                // Aqui você pode implementar lógica para perguntar ao usuário se deseja cadastrar automaticamente
                // Exemplo: salvar em sessão e redirecionar para tela de confirmação/cadastro
                // Por padrão, cancela a importação
                return back()->with('error', 'Cliente não encontrado. Cadastre o cliente antes de importar ou implemente cadastro automático.');
            }

            // Validação do exportador
            $nomeExportadorArquivo = $linha1[2] ?? null;
            $exportadorTaxIdArquivo = $linha1[1] ?? null;
            $exportador = \App\Models\Exportador::where('Exportador', $nomeExportadorArquivo)
                ->where('ExportadorTaxID', $exportadorTaxIdArquivo)
                ->where('empresa_id', $empresaLogada->id)
                ->first();

            if (!$exportador) {
                // Mesma lógica do cliente
                return back()->with('error', 'Exportador não encontrado. Cadastre o exportador antes de importar ou implemente cadastro automático.');
            }

            foreach ($lines as $line) {
                // Processar cada linha do arquivo TXT
                // Exemplo: Dividir a linha em campos e salvar no banco de dados
                $fields = explode('|', trim($line));
                $tipo = $fields[0];

                // Aqui você pode mapear os campos para o modelo correspondente e salvar
                if ($tipo == '0') {
                    // Linha 0: Cabeçalho do processo
                    $licenciamento = Licenciamento::create([
                        'estancia_id' => $fields[2] ?? null,
                        'descricao' => $fields[3] ?? null,
                        'empresa_id' => Auth::user()->empresas->first()->id,
                        'referencia_cliente' => $fields[7] ?? null,
                        // Adicione outros campos conforme necessário
                    ]);
                } elseif ($tipo == '1' && $licenciamento) {
                    // Linha 1: Informações do exportador e transporte
                    $licenciamento->update([
                        'tipo_transporte' => $fields[6] ?? null,
                        'registo_transporte' => $fields[7] ?? null,
                        'manifesto' => $fields[9] ?? null,
                        'factura_proforma' => $fields[10] ?? null,
                        'porto_entrada' => $fields[12] ?? null,
                        'tipo_declaracao' => $fields[13] ?? null,
                        'peso_bruto' => $fields[15] ?? null,
                        // Adicione outros campos conforme necessário
                    ]);
                } elseif ($tipo == '2' && $licenciamento) {
                    // Linha 2: Adições de mercadorias
                    $adicoes[] = [
                        'licenciamento_id' => $licenciamento->id,
                        'codigo_aduaneiro' => $fields[6] ?? null,
                        'quantidade_total' => $fields[7] ?? null,
                        'peso_total' => $fields[10] ?? null,
                        'moeda' => $fields[11] ?? null,
                        'preco_total' => $fields[12] ?? null,
                        // Adicione outros campos conforme necessário
                    ];
                }
            }

            // Salvar adições
            foreach ($adicoes as $adicao) {
                MercadoriaAgrupada::create($adicao);
            }

            DB::commit();
            return back()->with('success', 'Licenciamento importado com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao importar o arquivo TXT: ' . $e->getMessage());
        }
    }

    /**
     * Duplicar um licenciamento existente.
     */
    public function DuplicarLicenciamento($id)
    {
        try {
            DB::beginTransaction();

            $licenciamento = Licenciamento::findOrFail($id);
            $novoLicenciamento = $licenciamento->replicate();
            $novoLicenciamento->save();

            DB::commit();
            return back()->with('success', 'Licenciamento duplicado com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao duplicar o licenciamento: ' . $e->getMessage());
        }
    }

    /** PICE */
    public function pice()
    {
        // Lógica para exibir a lista PICE
        return view('licenciamentos.pice');
    }

}
