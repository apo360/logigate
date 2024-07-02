<?php

namespace App\Http\Controllers;

use App\Models\Pais;
use Illuminate\Http\Request;

class PaisController extends Controller
{

    public function list_cambios()
    {
        $cambios = Pais::select('moeda', 'cambio', 'data_cambio')->distinct('moeda')->get(); // Obter câmbios únicos por moeda
        return view('empresa.cambio', compact('cambios'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'moeda_txt' => 'required|string',
            'cambio' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'data_cambio' => 'required|date',
        ]);

        // Obter a moeda específica a partir do pedido
        $moeda = $request->input('moeda_txt');

        // Atualizar todos os países que têm a mesma moeda
        Pais::where('moeda', $moeda)->update([
            'cambio' => $request->input('cambio'),
            'data_cambio' => $request->input('data_cambio')
        ]);

        return redirect()->back()->with('success', 'Câmbio atualizado com sucesso');
    }
}
