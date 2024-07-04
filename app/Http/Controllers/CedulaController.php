<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CedulaService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class CedulaController extends Controller
{
    public function create(){
        return view('auth.verificar_cedula');
    }

    public function validar(Request $request)
    {
        $client = new Client();
        $chave = "345672010";

        $request->validate([
            'cedula' => 'required|string',
        ]);

        $N_cedula = $request->input('cedula');

        try {
            $response = $client->request('GET', 'https://cdoangola.co.ao/api/despachante/'. $N_cedula, [
                'query' => ['chave' => $chave],
            ]);

            $body = $response->getBody();
            $dados = json_decode($body, true);

            if (empty($dados)) {
                return redirect()->back()->withErrors(['cedula' => 'Cédula não encontrada ou inválida.']);
            }

            return view('auth.register', compact('dados'));

        } catch (RequestException $e) {
            // Log the exception message
            Log::error('Erro na requisição: ' . $e->getMessage());

            return redirect()->back()->withErrors(['cedula' => 'Ocorreu um erro ao validar a cédula. Por favor, tente novamente.']);
        } catch (\Exception $e) {
            // Log any other exceptions
            Log::error('Erro inesperado: ' . $e->getMessage());

            return redirect()->back()->withErrors(['cedula' => 'Ocorreu um erro inesperado. Por favor, tente novamente.']);
        }
    }

}
