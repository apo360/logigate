<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Models\Mercadoria;
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

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {

            DB::beginTransaction();
            
            Mercadoria::create([
                'preco_unitario' => $request['preco_unitario'],
                'Fk_Importacao' => $request['Fk_Importacao'],
                'preco_total' => $request['preco_total'],
                'Descricao' => $request['Descricao'],
                'NCM_HS' => $request['NCM_HS'],
                'NCM_HS_Numero' => $request['NCM_HS_Numero'],
                'Quantidade' => $request['Quantidade'],
                'Qualificacao' => $request['Qualificacao'],
                'Unidade' => 'Kg',
                'Peso' => $request['Peso'],
                'codigo_aduaneiro' =>  $request['codigo_aduaneiro'],
            ]);

            DB::commit();
    
            return response()->json([
                'message' => 'Mercadoria adicionado com Sucesso',
            ], 200);

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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Mercadoria $mercadoria)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Mercadoria $mercadoria)
    {
        //
    }
}
