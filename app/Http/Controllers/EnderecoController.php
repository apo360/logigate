<?php

namespace App\Http\Controllers;

use App\Models\Endereco;
use Illuminate\Http\Request;

class EnderecoController extends Controller
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
        $validatedData = $request->validate([
            'customer_id' => 'required',
            'BuildingNumber' => 'nullable|string|max:255',
            'StreetName' => 'nullable|string|max:255',
            'AddressDetail' => 'nullable|string|max:255',
            'AddressType' => 'nullable|string|max:255',
            'Province' => 'nullable|string|max:255',
            'City' => 'nullable|string|max:255',
            'PostalCode' => 'nullable|string|max:20',
            'Country' => 'nullable|string|max:255',
        ]);

        $endereco = Endereco::create($validatedData);
        
        return response()->json($endereco, 201); // Retorna o endereço criado com status 201
    }

    /**
     * Display the specified resource.
     */
    public function show(Endereco $endereco)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Endereco $endereco)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Endereco $endereco)
    {
        $validatedData = $request->validate([
            'BuildingNumber' => 'nullable|string|max:255',
            'StreetName' => 'nullable|string|max:255',
            'AddressDetail' => 'nullable|string|max:255',
            'AddressType' => 'nullable|string|max:255',
            'Province' => 'nullable|string|max:255',
            'City' => 'nullable|string|max:255',
            'PostalCode' => 'nullable|string|max:20',
            'Country' => 'nullable|string|max:255',
        ]);

        $endereco->update($validatedData);

        return response()->json($endereco, 200); // Retorna o endereço atualizado com status 200
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Endereco $endereco)
    {
        //
    }
}
