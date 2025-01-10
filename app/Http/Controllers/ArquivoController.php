<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\DocumentoAduRequest;
use App\Models\DocumentosAduaneiros;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ArquivoController extends Controller
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
    public function store(DocumentoAduRequest $request)
    {
        try {
            DB::beginTransaction();

            // Upload do arquivo para o S3
            if ($request->hasFile('arquivo')) {
                $file = $request->file('arquivo');
                $path = $file->store('documentos', 's3');
                $url = Storage::disk('s3')->url($path);
            }

            // Criar um novo documento no banco de dados
            $documentos = DocumentosAduaneiros::create([
                'processo_id' => $request->input('processo_id'),
                'licenciamento_id' => $request->input('licenciamento_id'),
                'TipoDocumento' => $request->input('TipoDocumento'),
                'NrDocumento' => $request->input('NrDocumento'),
                'DataEmissao' => $request->input('DataEmissao'),
                'Caminho' => $url, // URL do arquivo no S3
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Documento inserido com sucesso!',
                'documentos' => $documentos,
            ], 200);
        } catch (QueryException $e) {
            DB::rollBack();
            return response()->json([
                'error' => true,
                'message' => 'Erro ao inserir o documento!',
            ], 500);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(DocumentosAduaneiros $documentosAduaneiros)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DocumentosAduaneiros $documentosAduaneiros)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentosAduaneiros $documentosAduaneiros)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocumentosAduaneiros $documentoAduaneiro)
    {
        $documentoAduaneiro->delete();

        return response()->json([
            'success' => true,
            'message' => 'Documento excluido com sucesso!',
            'documentos' => $documentoAduaneiro,
        ], 200);
    }

    public function download($NrDocumento){
        
    }
}
