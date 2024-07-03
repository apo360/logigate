<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
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
        return view('empresa.edit', compact('empresa'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empresa $empresa)
    {
        $request->validate([
            'CodFactura' => 'required|string|max:20',
            'CodProcesso' => 'required|string|max:20',
            'Empresa' => 'required|string|max:200',
            'NIF' => 'required|string|max:50|unique:empresas,NIF,' . $empresa->id,
            'Cedula' => 'nullable|string|max:30|unique:empresas,Cedula,' . $empresa->id,
            'Logotipo' => 'nullable|file|image|max:2048',
            'Slogan' => 'nullable|string|max:100',
            'Endereco_completo' => 'required|string|max:200',
            'Provincia' => 'required|string|max:100',
            'Cidade' => 'required|string|max:100',
            'Dominio' => 'required|string|max:100',
            'Email' => 'required|email|max:100',
            'Fax' => 'nullable|string|max:100',
            'Contacto_movel' => 'required|string|max:100',
            'Contacto_fixo' => 'nullable|string|max:100',
            'Sigla' => 'required|string|max:45',
        ]);

        $empresa->fill($request->all());

        if ($request->hasFile('Logotipo')) {
            $path = $request->file('Logotipo')->store('logotipos', 'public');
            $empresa->Logotipo = $path;
        }

        $empresa->save();

        return redirect()->route('empresas.index')->with('success', 'Empresa atualizada com sucesso.');
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
