<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerAvenca;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAvencaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Filtra apenas os clientes que têm avenças associadas
        $clientes = Customer::where('empresa_id', Auth::user()->empresas->first()->id ?? null)
        ->has('avencas') // Garante que o cliente tenha ao menos uma avença
        ->with('avencas')->get(); // Carrega também as avenças associadas
        
        // Retorna para a view com os dados dos clientes (incluindo suas avenças)
        return view('customer.avenca_index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $clientes = Customer::where('empresa_id', Auth::user()->empresas->first()->id ?? null)->get(); // Busca todos os clientes
        return view('customer.avenca_create', compact('clientes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validação dos dados
        $validatedData = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'valor' => 'required|numeric|min:0',
            'periodicidade' => 'required|string',
            'data_inicio' => 'required|date',
            'data_fim' => 'nullable|date|after_or_equal:data_inicio',
            'ativo' => 'sometimes|boolean'
        ]);

        // Criação da avença
        CustomerAvenca::create($validatedData);

        return redirect()->route('avenca.index')->with('success', 'Avença criada com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(CustomerAvenca $customerAvenca)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CustomerAvenca $customerAvenca)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CustomerAvenca $customerAvenca)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CustomerAvenca $customerAvenca)
    {
        //
    }
}
