<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\MercadoriaRequest;
use App\Models\Licenciamento;
use App\Models\Mercadoria;
use App\Models\MercadoriaAgrupada;
use App\Models\PautaAduaneira;
use App\Models\Processo;
use App\Models\Subcategoria;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MercadoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    // MercadoriaController.php
    public function getCodigosAduaneiros($cod_pauta)
    {
        // Busca os códigos aduaneiros que começam com o código da subcategoria
        $codigos = PautaAduaneira::where('codigo', 'like', $cod_pauta . '%')->get();

        // Retorna como JSON para ser usado na resposta AJAX
        return response()->json($codigos);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($licenciamento_id = null, $processo_id = null)
    {
        // Inicializa variáveis para controle
        $licenciamento = null; $processo = null; $mercadorias = null;

        // Verifica se o licenciamento_id foi passado
        if ($licenciamento_id) 
        { 
            $licenciamento = Licenciamento::findOrFail($licenciamento_id);
            MercadoriaAgrupada::recalcularAgrupamento($licenciamento_id);
        }

        // Verifica se o processo_id foi passado
        if ($processo_id) { 
            $processo = Processo::find($processo_id);
            $mercadorias = Mercadoria::where('Fk_Importacao', $processo_id)->get(); 
            MercadoriaAgrupada::recalcularAgrupamento(null, $processo_id);
        }

        

        $pautaAduaneira = PautaAduaneira::all();

        $sub_categorias = Subcategoria::all();

        // Redireciona para o formulário de mercadorias com os dados apropriados
        return view('mercadorias.create_mercadoria', compact('licenciamento', 'processo', 'pautaAduaneira', 'sub_categorias'));
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(MercadoriaRequest $request)
    {
        try {
            DB::beginTransaction();
            
            // Validating the request
            $validatedData = $request->validated();

            // Creating a new mercadoria entry
            $mercadoria = Mercadoria::create($validatedData);

            // Atualizar agrupamento de mercadorias
            MercadoriaAgrupada::StoreAndUpdateAgrupamento($mercadoria);

            // Atualização de licenciamento, caso o mesmo exista
            if ($request->has('licenciamento_id')) {
                $licenciamento = Licenciamento::where('id', $validatedData['licenciamento_id'])->first();

                // Atualizar valores agregados de FOB e peso
                $licenciamento->fob_total += $mercadoria->preco_total;
                $licenciamento->peso_bruto +=  $mercadoria->Peso;

                $licenciamento->save();
            }

            // Atualização de processo, caso o mesmo exista
            if ($request->has('processo_id')) {
                $processo = Processo::where('id', $validatedData['processo_id'])->first();
                // 
                
            }

            DB::commit();

            // Returning success message or redirecting
            return redirect()->back()->with('success', 'Mercadoria criada com sucesso!');

        } catch (QueryException $e) {
            DB::rollBack();

            return DatabaseErrorHandler::handle($e, $request);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Mercadoria $mercadoria)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Mercadoria $mercadoria)
    {
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MercadoriaRequest $request, Mercadoria $mercadoria)
    {
        // Find the existing mercadoria by ID
        $mercadoria = Mercadoria::findOrFail($mercadoria->id);

        // Validating the request
        $validatedData = $request->validated();

        // Updating the mercadoria with new data
        $mercadoria->update($validatedData);

        MercadoriaAgrupada::StoreAndUpdateAgrupamento($mercadoria);

        // Returning success message or redirecting
        return response()->json([
            'message' => 'Mercadoria atualizada com sucesso!',
            'mercadoria' => $mercadoria
        ], 200);
    }

    /**
     * MercadoriaController.php
     * Remove the specified resource from storage.
     */
    public function destroy(Mercadoria $mercadoria)
    {
        try {
            DB::beginTransaction();
            $licenciamento = Licenciamento::find($mercadoria->licenciamento_id);
            if ($licenciamento) {
                $licenciamento->fob_total -= $mercadoria->preco_total;
                $licenciamento->peso_bruto -= $mercadoria->Peso;
                $licenciamento->save();
            }

            $mercadoria->delete();
            MercadoriaAgrupada::RemoveAgrupamento($mercadoria);
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Mercadoria excluída com sucesso!', 'mercadoria' => $mercadoria], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => true, 'message' => 'Erro ao excluir a mercadoria. Tente novamente.'], 500);
        }
    }
}
