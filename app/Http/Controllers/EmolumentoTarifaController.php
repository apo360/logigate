<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\TarifaDURequest;
use App\Models\EmolumentoTarifa;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmolumentoTarifaController extends Controller
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
    public function store(TarifaDURequest $request)
    {
        try {

            DB::beginTransaction();

            // Cria o processo e obtém a instância completa
            EmolumentoTarifa::create($request->validated());

            DB::commit();

            return redirect()->back()->with('success', 'Emolumentos Inseridos com sucesso!');
            
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
    public function show(EmolumentoTarifa $emolumentoTarifa)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EmolumentoTarifa $emolumentoTarifa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TarifaDURequest $request, EmolumentoTarifa $emolumentoTarifa)
    {
        try {

            DB::beginTransaction();

            // Cria o processo e obtém a instância completa
            EmolumentoTarifa::where('id', $emolumentoTarifa->id)->update($request->validated());

            DB::commit();

            return redirect()->back()->with('success', 'Emolumentos Actualizados com sucesso!');
            
        } catch (QueryException $e) {
            DB::rollBack();
            return DatabaseErrorHandler::handle($e, $request);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Erro inesperado: ' . $e->getMessage(),], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(EmolumentoTarifa $emolumentoTarifa)
    {
        //
    }
}
