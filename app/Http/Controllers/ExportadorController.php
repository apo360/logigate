<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\ExportadorRequest;
use App\Models\Exportador;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExportadorController extends Controller
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
    public function store(ExportadorRequest $request)
    {
        // Determine the form type based on the request data
        $formType = $request->get('formType'); 

        try {
            // Inicia uma transação para garantir a integridade dos dados
            DB::beginTransaction();

            $user = Auth::user();

            $exportValidate = $request->validated();

            $exportValidate['user_id'] = $user->id;

            $exportValidate['empresa_id'] = $user->empresas->first()->id;
            // Cria um novo registro de cliente na tabela 'customers' com os dados fornecidos
            $newExportador = Exportador::create($exportValidate);

            // Confirma a transação, salvando as alterações no banco de dados
            DB::commit();

            // Prepare success response based on form type
            if ($formType === 'modal') {
                // Return success data for modal forms
                return response()->json([
                    'message' => 'Exportador adicionado com Sucesso',
                    'exportador_id' => $newExportador->id,
                    'codCli' => $newExportador->CustomerTaxID,
                ], 200);
            } else {
                // Redirect to 'form.edit' for the main form
                return redirect()->route('exportadors.edit', $newExportador->id)->with('success', 'Cliente Inserido com sucesso');
            }
            
        } catch (QueryException $e) { 
            DB::rollBack();

            return DatabaseErrorHandler::handle($e, $request);
        } 

    }

    /**
     * Display the specified resource.
     */
    public function show(Exportador $exportador)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exportador $exportador)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Exportador $exportador)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exportador $exportador)
    {
        //
    }
}
