<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Models\Licenciamento;
use App\Models\MercadoriaAgrupada;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\LicenciamentoService;
use Illuminate\Support\Facades\DB;

class LicenciamentoController extends AuthenticatedController
{
    public function __construct(private readonly LicenciamentoService $licenciamentoService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Retornar a view com os licenciamentos paginados
        return view('Licenciamento.index');
    }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // A lógica de criação agora é tratada pelo Livewire, então apenas retornamos a view que contém o componente Livewire.
        return view('Licenciamento.create', [
                'customer_id' => $request->query('customer_id')
            ]);
    }

    public function storeDraft(Request $request){
        try {
            $this->licenciamentoService->createDraft($request->all(), $this->empresa->id);

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
        // A lógica de exibição agora é tratada pelo Livewire, então apenas retornamos a view que contém o componente Livewire.
        return view('Licenciamento.show', compact('licenciamento'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Licenciamento $licenciamento)
    {
        // Continue com o processo de edição
        return view('Licenciamento.edit', compact('licenciamento'));
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
}
