<?php

namespace App\Http\Controllers;

use App\Models\ActivatedModule;
use App\Models\Empresa;
use App\Models\MetodoPagamento;
use App\Models\Module;
use App\Models\Pagamento;
use App\Models\Subscricao;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ModuleSubscriptionController extends Controller
{
    public function index()
    {
        $modules = Module::all();
        $empresa = Auth::user()->empresas->first(); // Supondo que o usuário está associado a uma empresa

        return view('modules.index', compact('modules', 'empresa'));
    }

    public function show(Empresa $empresa)
    {
        $modulos = Module::all();
        $metodosPagamento = MetodoPagamento::all();
        return view('empresa.subscricao', compact('empresa', 'modulos', 'metodosPagamento'));
    }

    public function pay(Request $request)
    {
        $selectedModulos = $request->input('selected_modulos', []);
        $totalPrice = $request->input('total_price', 0);

        $request->validate([
            'module_id' => 'required|exists:modules,id',
            'empresa_id' => 'required|exists:empresas,id',
        ]);

        foreach ($selectedModulos as $moduloId) {
            Subscricao::create([
                'empresa_id' => $request->input('empresaId'),
                'modulo_id' => $moduloId,
                'data_expiracao' => now()->addYear(),
                'status' => 'ATIVA'
            ]);

            ActivatedModule::create([
                'module_id' => $moduloId,
                'empresa_id' => $request->input('empresaId'),
                'activation_date' => now(),
            ]);
        }

        // Processar o pagamento
        Pagamento::create([
            'empresa_id' => $request->input('empresaId'),
            'metodo_pagamento_id' => $request->input('metodo_pagamento_id'),
            'valor' => $totalPrice,
        ]);

        return redirect()->route('modules.index')->with('success', 'Módulo subscrito com sucesso!');
    }
}
