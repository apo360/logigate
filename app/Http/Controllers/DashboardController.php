<?php

namespace App\Http\Controllers;

use App\Models\Processo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    
    public function index(){

        $processesByCountries = Processo::where('empresa_id', Auth::user()->empresas->first()->id)
            ->join('importacao', 'importacao.processo_id', '=', 'processos.id')
            ->join('paises', 'importacao.Fk_pais_origem', '=', 'paises.id')
            ->select('paises.pais as paisss', DB::raw('count(processos.id) as total'))
            ->groupBy('paises.pais')
            ->orderByDesc('total')
            ->limit(7)
            ->get();
            
        $topCountries = Processo::where('empresa_id', Auth::user()->empresas->first()->id)
            ->join('importacao', 'processos.id', '=', 'importacao.processo_id')
            ->join('paises', 'importacao.Fk_pais_origem', '=', 'paises.id')
            ->select('paises.pais', DB::raw('count(processos.id) as total'))
            ->groupBy('paises.pais')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        $processesByCustomer = DB::table('processos')
            ->join('customers', 'processos.customer_id', '=', 'customers.id')
            ->select('customers.CompanyName', DB::raw('count(processos.id) as total'))
            ->groupBy('customers.CompanyName')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard', compact('topCountries', 'processesByCustomer', 'processesByCountries'));
    }

}
