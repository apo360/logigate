<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\ProcessoLicenciamentoMercadoria;

class ProcessoLicenciamentoMercadoriaController extends Controller
{
    public function store(Request $request)
    {
        // Validação dos dados
        $validated = $request->validate([
            'mercadorias' => 'required|array', // Espera-se um array de mercadorias
            'mercadorias.*.mercadoria_id' => 'required|exists:mercadorias,id',
            'mercadorias.*.quantidade' => 'required|integer|min:1',
            'processo_id' => 'nullable|exists:processos,id', // processo opcional
            'licenciamento_id' => 'nullable|exists:licenciamentos,id' // licenciamento opcional
        ]);

        // Loop para criar vários registros de mercadorias
        foreach ($validated['mercadorias'] as $mercadoria) {
            ProcessoLicenciamentoMercadoria::create([
                'mercadoria_id' => $mercadoria['mercadoria_id'],
                'quantidade' => $mercadoria['quantidade'],
                'processo_id' => $validated['processo_id'] ?? null,
                'licenciamento_id' => $validated['licenciamento_id'] ?? null,
            ]);
        }

        // Retornar sucesso
        return response()->json([
            'message' => 'Mercadorias adicionadas com sucesso!'
        ], 201);
    }
}

