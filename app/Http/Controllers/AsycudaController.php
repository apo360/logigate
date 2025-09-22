<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Services\OpenAIService;

class AsycudaController extends Controller
{
    public function listarXMLs()
    {
        // Usar disco 'public'
        $files = Storage::disk('public')->files('');

        // filtrar apenas arquivos .xml
        $xmlFiles = array_filter($files, function ($file) {
            return pathinfo($file, PATHINFO_EXTENSION) === 'xml';
        });

        return view('processos.du', compact('xmlFiles'));
    }

    // Função de upload ASYCUDA XML
    public function uploadXML(Request $request)
    {
        $request->validate([
            'xml_file' => 'required|mimes:xml|max:2048', // máximo 2MB
        ]);

        $file = $request->file('xml_file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('', $fileName, 'public');

        return redirect()->route('asycuda.listar')->with('success', 'Ficheiro carregado com sucesso!');
    }

    public function analisarDeclaracao($file)
    {
        $filePath = storage_path('app/public/' . $file);

        if (!file_exists($filePath)) {
            abort(404, 'Arquivo não encontrado');
        }

        $openAI = new OpenAIService();

        $respostaIA = $openAI->analisarXMLAsycuda($filePath);

        return view('processos.du_asycuda_resultado', compact('respostaIA'));
    }
}
