<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Domains\Exportadores\Actions\CreateOrAssociateExportadorAction;
use App\Domains\Exportadores\Actions\DeleteExportadorAction;
use App\Domains\Exportadores\Actions\UpdateExportadorAssociationAction;
use App\Domains\Exportadores\Actions\UpdateExportadorProfileAction;
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
        $exportadors = $this->empresa->exportadors()->get();

        return view('exportadors.index', compact('exportadors'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
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
    public function show(Exportador $exportador)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Exportador $exportador)
    {
        $paises = Pais::all();

        return view('exportadors.edit', compact('exportador', 'paises'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        ExportadorRequest $request,
        $id,
        UpdateExportadorProfileAction $updateProfile,
        UpdateExportadorAssociationAction $updateAssociation
    )
{
    try {
        $user = Auth::user();
        $escopo = $request->get('escopo', 'local'); // valor padrão: local

        // Encontra o exportador
        $exportador = Exportador::findOrFail($id);
        $data = ExportadorFormData::fromArray($request->validated());

        // --- ESCOPOS DE ATUALIZAÇÃO ---
        if ($escopo === 'global') {
            /**
             * Atualização Global
             * Só permitida para administradores ou utilizadores com permissão
             * global. Aqui alteramos os dados centrais do exportador.
             */
            if ($user->hasRole('admin') || $user->can('update-global-exportador')) {
                $exportador = $updateProfile->execute($exportador, $data);
            } else {
                throw new \Exception('Sem permissão para atualização global.');
            }

        } else {
            /**
             * Atualização Local
             * Apenas altera os dados da associação (pivot) e não os dados centrais
             * da tabela "exportadors".
             */
            $exportador = $updateAssociation->execute($exportador, $this->empresa, $data);
        }

        // Resposta AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $escopo === 'global'
                    ? 'Exportador atualizado globalmente com sucesso!'
                    : 'Exportador atualizado localmente com sucesso!',
                'exportador_id' => $exportador->id,
            ]);
        }

        // Resposta padrão (redirect)
        return redirect()
            ->route('exportadors.edit', $exportador->id)
            ->with('success', $escopo === 'global'
                ? 'Exportador atualizado globalmente com sucesso!'
                : 'Exportador atualizado localmente com sucesso!');

    } catch (\Exception $e) {
        if ($request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage(),
            ], 500);
        }

        return back()->withErrors('Erro: ' . $e->getMessage());
    }
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
