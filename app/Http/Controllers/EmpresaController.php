<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Models\Empresa;
use App\Models\Municipio;
use App\Models\Provincia;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmpresaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtém o usuário autenticado
        $user = Auth::user();

        // Obtém as empresas associadas ao usuário autenticado
        $empresas = $user->empresas;

        return view('empresa.index', compact('empresas'));
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Empresa $empresa)
    {
        return view('empresa.show', compact('empresa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Empresa $empresa)
    {
        $provincias = Provincia::all();
        $cidades = Municipio::all();
        return view('empresa.edit', compact('empresa', 'provincias', 'cidades'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empresa $empresa)
    {
        try {
            $request->validate([
                // 'Logotipo' => 'nullable|file|image|max:2048',
                'Slogan' => 'nullable|string|max:100',
                'Endereco_completo' => 'required|string|max:200',
                'Provincia' => 'required|string|max:100',
                'Cidade' => 'required|string|max:100',
                'Fax' => 'nullable|string|max:100',
                'Contacto_fixo' => 'nullable|string|max:100',
            ]);
    
            $empresa->fill($request->all());
    
            /*if ($request->hasFile('Logotipo')) {
                $path = $request->file('Logotipo')->store('logotipos', 'public');
                $empresa->Logotipo = $path;
            }*/
    
            $empresa->save();

            return response()->json([
                'message' => 'Empresa actualizada com Sucesso',
            ], 200);

        } catch (QueryException $e) { 

            return DatabaseErrorHandler::handle($e, $request);
        } 

        // 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Empresa $empresa)
    {
        $empresa->delete();

        return redirect()->route('empresas.index')->with('success', 'Empresa excluída com sucesso.');
    }
}
