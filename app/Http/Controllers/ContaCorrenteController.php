<?php

namespace App\Http\Controllers;

use App\Models\ContaCorrente;
use App\Models\Customer;
use Illuminate\Http\Request;

class ContaCorrenteController extends Controller
{
    //

    public function create($cliente_id)
    {
        $cliente = Customer::findOrFail($cliente_id);
        return view('conta_corrente.create', compact('cliente'));
    }

    public function store(Request $request, $cliente_id)
    {
        $request->validate([
            'valor' => 'required|numeric',
            'tipo' => 'required|in:credito,debito',
            'descricao' => 'nullable|string',
            'data' => 'required|date',
        ]);

        ContaCorrente::create([
            'cliente_id' => $cliente_id,
            'valor' => $request->valor,
            'tipo' => $request->tipo,
            'descricao' => $request->descricao,
            'data' => $request->data,
        ]);

        return redirect()->route('cliente.cc', $cliente_id)->with('success', 'Transação registrada com sucesso!');
    }
}
