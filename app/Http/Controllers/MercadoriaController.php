<?php

namespace App\Http\Controllers;

use App\Models\Mercadoria;
use Illuminate\Http\Request;

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
        ]);

        return response()->json([
            'message' => 'Mercadoria adicionado com Sucesso',
        ], 200);
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
