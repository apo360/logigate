<?php

namespace App\Http\Controllers;

use App\Models\ProcessoDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProcessoDraftController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $empresa = Auth::user()->empresas->first();
        $processos_drafts = ProcessoDraft::where('empresa_id', $empresa->id)->orderBy('DataAbertura', 'desc')->get();
        return response()->json($processos_drafts, 201);
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
        $processo = ProcessoDraft::create($request->all());
        return response()->json($processo, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $processo = ProcessoDraft::findOrFail($id);
        return response()->json($processo);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProcessoDraft $processoDraft)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $processo = ProcessoDraft::findOrFail($id);

        $processo->update($request->all());
        return response()->json($processo);
    }

    /**
     * Remove the specified resource from storage.
     */
     // Método para excluir um rascunho
    public function destroy($id)
    {
        $processo = ProcessoDraft::findOrFail($id);
        $processo->delete();
        return response()->json(null, 204);
    }
}
