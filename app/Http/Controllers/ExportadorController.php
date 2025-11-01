<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\ExportadorRequest;
use App\Models\Exportador;
use App\Models\Pais;
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
    public function store(ExportadorRequest $request)
    {
        $formType = $request->get('formType'); 

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $empresa = $user->empresas->first();

            $dadosValidados = $request->validated();

            // Verifica se o exportador já existe globalmente
            $exportador = Exportador::where('ExportadorTaxID', $dadosValidados['ExportadorTaxID'])
                ->where('Exportador', $dadosValidados['Exportador'])
                ->first();

            // Se não existir, cria um novo
            if (!$exportador) {
                $exportador = Exportador::create($dadosValidados);
            }

            // Verifica se já está associado à empresa
            $jaAssociado = $exportador->empresas()
                ->where('empresa_id', $empresa->id)
                ->exists();

            if (!$jaAssociado) {
                $exportador->empresas()->attach($empresa->id, [
                    'codigo_exportador' => $dadosValidados['codigo_exportador'] ?? null,
                    'additional_info' => $dadosValidados['additional_info'] ?? null,
                    'status' => $dadosValidados['status'] ?? 'ATIVO',
                    'data_associacao' => now(),
                ]);
            }

            DB::commit();

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
        $paises = [
            'AO' => 'Angola',
            'BR' => 'Brasil',
            'PT' => 'Portugal',
            'US' => 'Estados Unidos',
            // Adicione mais países conforme necessário
        ];

        return view('exportadors.edit', compact('exportador', 'paises'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ExportadorRequest $request, $id)
{
    try {
        DB::beginTransaction();

        $user = Auth::user();
        $empresa = $user->empresas->first();
        $dadosValidados = $request->validated();
        $escopo = $request->get('escopo', 'local'); // valor padrão: local

        // Encontra o exportador
        $exportador = Exportador::findOrFail($id);

        // Verifica se está associado à empresa
        $associacao = $exportador->empresas()
            ->where('empresa_id', $empresa->id)
            ->first();

        // --- ESCOPOS DE ATUALIZAÇÃO ---
        if ($escopo === 'global') {
            /**
             * Atualização Global
             * Só permitida para administradores ou utilizadores com permissão
             * global. Aqui alteramos os dados centrais do exportador.
             */
            if ($user->hasRole('admin') || $user->can('update-global-exportador')) {
                $exportador->update($dadosValidados);
            } else {
                throw new \Exception('Sem permissão para atualização global.');
            }

        } else {
            /**
             * Atualização Local
             * Apenas altera os dados da associação (pivot) e não os dados centrais
             * da tabela "exportadors".
             */
            if ($associacao) {
                $exportador->empresas()->updateExistingPivot($empresa->id, [
                    'codigo_exportador' => $dadosValidados['codigo_exportador'] ?? $associacao->pivot->codigo_exportador,
                    'additional_info'   => $dadosValidados['additional_info'] ?? $associacao->pivot->additional_info,
                    'status'            => $dadosValidados['status'] ?? $associacao->pivot->status,
                    'data_associacao'   => $associacao->pivot->data_associacao ?? now(),
                ]);
            } else {
                // Cria associação se não existir
                $exportador->empresas()->attach($empresa->id, [
                    'codigo_exportador' => $dadosValidados['codigo_exportador'] ?? null,
                    'additional_info'   => $dadosValidados['additional_info'] ?? null,
                    'status'            => $dadosValidados['status'] ?? 'ATIVO',
                    'data_associacao'   => now(),
                ]);
            }
        }

        DB::commit();

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
        DB::rollBack();

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
    public function destroy(Exportador $exportador)
    {
        //
    }
}
