<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Domains\Exportadores\Actions\CreateOrAssociateExportadorAction;
use App\Domains\Exportadores\Actions\DeleteExportadorAction;
use App\Domains\Exportadores\Data\ExportadorFormData;
use App\Http\Requests\ExportadorRequest;
use App\Models\Exportador;
use App\Models\Pais;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class ExportadorController extends AuthenticatedController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Exportador::class);
        $exportadors = $this->empresa->exportadors()->get();

        return view('exportadors.index', compact('exportadors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Exportador::class);
        $paises = Pais::all();
        return view('exportadors.create', compact('paises'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ExportadorRequest $request, CreateOrAssociateExportadorAction $action)
    {
        $formType = $request->get('formType'); 

        try {
            $user = Auth::user();
            $exportador = $action->execute(
                ExportadorFormData::fromArray($request->validated()),
                $this->empresa,
                $user
            );

            // Retorno diferenciado por tipo de formulário
            if ($formType === 'modal') {
                return response()->json([
                    'message' => 'Exportador adicionado com sucesso!',
                    'exportador_id' => $exportador->id,
                    'codCli' => $exportador->ExportadorTaxID,
                ], 200);
            }

            return redirect()
                ->route('exportadors.edit', $exportador->id)
                ->with('success', 'Exportador adicionado com sucesso!');

        } catch (QueryException $e) { 
            return DatabaseErrorHandler::handle($e, $request);
        } 
    }


    /**
     * Display the specified resource.
     */
    public function show(Exportador $exportador, \App\Domains\Exportadores\Queries\ExportadorDetailQuery $query)
    {
        return view('exportadors.show', $query->execute($exportador->id, $this->empresa));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exportador $exportador)
    {
        $this->authorize('update', $exportador);
        $paises = Pais::all();

        return view('exportadors.edit', [
            'exportador' => $exportador,
            'paises' => $paises,
            'association' => $exportador->empresas()->where('empresas.id', $this->empresa->id)->firstOrFail()->pivot,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ExportadorRequest $request, Exportador $exportador, \App\Domains\Exportadores\Actions\UpdateExportadorAction $action)
    {
        $action->execute($exportador, $this->empresa, ExportadorFormData::fromArray($request->validated()), $request->input('escopo', 'local'));
        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Exportador atualizado com sucesso.', 'exportador_id' => $exportador->id]);
        }
        return redirect()->route('exportadors.show', $exportador->id)->with('success', 'Exportador atualizado com sucesso.');
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Exportador $exportador, DeleteExportadorAction $action)
    {
        $action->execute($exportador, $this->empresa, Auth::user());

        return redirect()
            ->route('exportadors.index')
            ->with('success', 'Exportador removido da empresa com sucesso.');
    }
}
