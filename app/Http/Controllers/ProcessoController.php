<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Helpers\PdfHelper;
use App\Http\Requests\DARRequest;
use App\Http\Requests\PortuariaRequest;
use App\Http\Requests\TarifaDURequest;
use App\Models\Customer;
use App\Models\Exportador;
use App\Models\Importacao;
use App\Models\Mercadoria;
use App\Models\Pais;
use App\Models\Processo;
use App\Models\views\ProcessosView;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Customer::where('empresa_id', Auth::user()->empresas->first()->id ?? null)->get(); // Busca os Clientes
        $exportador = Exportador::where('empresa_id', Auth::user()->empresas->first()->id ?? null)->get();
        $NewProcesso = Processo::generateNewProcesso(); // Inicializar com novo código de processo
        $paises = Pais::all();
        
        // chamar a stored procedure
        $newCustomerCode = Customer::generateNewCode();
        $newExportadorCode = Exportador::generateNewCode();

        // Retornar uma view com o formulário para criar um novo processo
        return view('processos.create', compact('clientes', 'exportador', 'NewProcesso', 'paises', 'newCustomerCode', 'newExportadorCode'));
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
                'Situacao' => 'required|string|in:Em processamento,Desembarcado,Retido,Concluido',
                'exportador_id' => 'required|string',
            ]);
    
            $processo_request['user_id'] = $user->id;
            $processo_request['empresa_id'] = $user->empresas->first()->id;

            // Cria o processo
            $processo = Processo::create($processo_request);

            // Cria a importação
            $importacao = Importacao::create([
                'processo_id' => $processo->id,
                'Fk_pais_origem' => $request->input('Fk_pais'),
                'PortoOrigem' => $request->input('PortoOrigem'),
                'TipoTransporte' => $request->input('TipoTransporte'),
                'NomeTransporte' => $request->input('NomeTransporte'),
                'DataChegada' => $request->input('DataChegada'),
                'MarcaFiscal' => $request->input('MarcaFiscal'),
                'BLC_Porte' => $request->input('BLC_Porte'),
                'Moeda' => $request->input('Moeda'),
                'FOB' => $request->input('FOB'), 
                'Freight' => $request->input('Freight'), //Frete
                'Insurance' => $request->input('Insurance'), // Seguro
                'Cambio' => $request->input('Cambio'),
                'ValorAduaneiro' => $request->input('ValorAduaneiro'),
                'ValorTotal' => $request->input('ValorTotal'),
            ]);

            DB::commit();

            // Redirecione para a página de listagem de processos com uma mensagem de sucesso
            return redirect()->route('processos.index')->with('success', 'Processo inserido com sucesso!');
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
        $mercadorias = Mercadoria::where('Fk_Importacao', $processo->importacao->id)->get();
        return view('processos.show', compact('processo', 'mercadorias'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, $processoID)
    {
        $processo = Processo::findOrFail($processoID);
        
        $mercadorias = Mercadoria::where('Fk_Importacao', $processo->importacao->id)->get(); // Obtenha a relação 'mercadoria'
        
        return view('processos.edit', compact('processo', 'mercadorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $ProcessoRequest,  DARRequest $DARRequest, TarifaDURequest $DURequest, PortuariaRequest $Portuaria, $processoID)
    {
        
        try {

            // Inicia uma transação para garantir a integridade dos dados
            DB::beginTransaction();

            // Actualizar os campos de processos...
            Processo::where('id', $processoID)->update([
                'Situacao' => $ProcessoRequest->input('Situacao'),
            ]); // Dados do Processo

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
}
