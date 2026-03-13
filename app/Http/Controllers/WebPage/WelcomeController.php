<?php

namespace App\Http\Controllers\WebPage;
use App\Http\Controllers\BaseController;

use App\Models\Plano;
use Illuminate\Http\Request;

class WelcomeController extends BaseController
{
    //
    public function index(){

        $planos = Plano::all();

        return view('welcome', compact('planos'));
    }

    public function consultarPauta(){
        return view('WebSite.consultar_pauta');
    }

    //
    public function CheckoutPlan($planID, Request $request){

        $modalidade = $request->query('modalidade', 'monthly');

        $planoSelecionado = Plano::where('id', $planID)->first();

        // Validar ciclo
        if (! in_array($modalidade, ['monthly', 'semestral', 'annual'])) {
            abort(400, 'Modalidade Inválida');
        }

        // Calcular preço
        $price = match ($modalidade) {
            'monthly'   => $planoSelecionado->preco_mensal,
            'semestral' => $planoSelecionado->preco_semestral,
            'annual'    => $planoSelecionado->preco_anual,
        };

        // Redireciona para cadastro rápido
        return view('WebSite.MinCadastro', compact('planoSelecionado', 'price'));
    }

    public function marketplace(){
        return view('WebSite.marketplace');
    }
}
