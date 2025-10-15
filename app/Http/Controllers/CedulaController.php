<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CedulaService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use App\Models\Empresa;

class CedulaController extends Controller
{
    public function create(){
        return view('auth.verificar_cedula');
    }

    // Validar a cédula usando a API externa
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

    // Validar a cédula usando o serviço CedulaService
    public function validarCedula(Request $request, CedulaService $cedulaService)
    {
        $request->validate([
            'cedula' => 'required|string',
        ]);

        $cedula = $request->input('cedula');

        // Chama o serviço para validar a cédula
        $resultado = $cedulaService->validarCedula($cedula);

        if ($resultado['status'] === 'success') {
            return view('auth.register', ['dados' => $resultado['data']]);
        }

        return redirect()->back()->withErrors(['cedula' => $resultado['message']]);
    }

    // Validar a existencia da cédula na Base de Dados local
    public function validarCedulaLocal(Request $request)
    {
        $request->validate([
            'cedula' => 'required|string',
        ]);

        $cedula = $request->input('cedula');

        // Verificar se existe alguma cedula na base de dados local já registada
        $cedulaExistente = Empresa::where('Cedula', $cedula)->first();
        if ($cedulaExistente) {
            return response()->json(['valid' => false, 'message' => 'O Nº da Cédula já existe.'], 422);
        }
        return response()->json(['valid' => true, 'message' => 'Cédula válida e disponível para registo.']);
    }
}
