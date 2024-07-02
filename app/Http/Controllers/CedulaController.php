<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CedulaService;
use GuzzleHttp\Client;

class CedulaController extends Controller
{
    public function create(){
        return view('auth.verificar_cedula');
    }

    public function validar(Request $request){

        $client = new Client();

        $chave = "345672010";

        $request->validate([
            'cedula' => 'required|string',
        ]);

        $N_cedula = $request->input('cedula');

        $response = $client->request('GET', 'https://cdoangola.co.ao/api/despachante/'. $N_cedula, [
            'query' => ['chave' => $chave],
        ]);

        $body = $response->getBody();
        $dados = json_decode($body, true);

        return view('auth.register', compact('dados'));
    }
}
