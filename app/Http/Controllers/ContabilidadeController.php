<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContabilidadeController extends Controller
{
    /** Contas */

    public function contas()
    {
        return view('contabilidade.contas.index');
    }

    /** Lançamentos */
    public function lancamentos()
    {
        return view('contabilidade.lancamentos.index');
    }

    /** Relatórios */
    public function relatorios()
    {
        return view('contabilidade.relatorios.index');
    }

    /** Configurações */
    public function configuracoes()
    {
        return view('contabilidade.configuracoes.index');
    }

    /** Plano de Contas */
    public function planoContas()
    {
        return view('contabilidade.plano_contas.index');
    }

    /** Mapa */
    public function mapa()
    {
        return view('contabilidade.mapa.index');
    }

    /** Balanço */
    public function balanco()
    {
        return view('contabilidade.balanco.index');
    }
}
