<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Models\Empresa;
use App\Models\EmpresaBanco;
use App\Models\Municipio;
use App\Models\Provincia;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;

class EmpresaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtém o usuário autenticado
        $user = Auth::user();

        // Obtém as empresas associadas ao usuário autenticado
        $empresas = $user->empresas;

        return view('empresa.index', compact('empresas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function storeLogo(Request $request)
    {
        // Validação do logotipo
        $request->validate([
            'logotipo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $empresaId = Auth::user()->empresas->first()->id;

        $empresa = Empresa::findOrFail($empresaId);

        // Configurando o S3 Client
        $s3 = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        if ($empresa->logotipo) {
            $key = str_replace(env('AWS_URL') . '/', '', $empresa->logotipo);
        
            $s3->deleteObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key'    => $key,
            ]);
        }
        

        if ($request->hasFile('logotipo')) {
            try {
                // Nome único para o arquivo
                $fileName = 'Logotipos/' . uniqid() . '_' . $request->file('logotipo')->getClientOriginalName();

                // Fazer upload para o S3
                $result = $s3->putObject([
                    'Bucket' => env('AWS_BUCKET'),
                    'Key'    => $fileName,
                    'SourceFile' => $request->file('logotipo')->getPathName(),
                ]);

                // URL do logotipo no S3
                $logotipoUrl = $result['ObjectURL'];

                // Atualizar a empresa no banco de dados
                $empresa->logotipo = $logotipoUrl;
            } catch (AwsException $e) {
                DatabaseErrorHandler::handle($e, $request);
                return redirect()->back()->withErrors(['logotipo' => 'Erro ao fazer upload para o S3: ' . $e->getMessage()]);
            }
        }

        $empresa->save();

        return redirect()->back()->with('success', 'Logotipo atualizada com sucesso.');

    }

    /**
     * Display the specified resource.
     */
    public function show(Empresa $empresa)
    {
        return view('empresa.show', compact('empresa'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Empresa $empresa)
    {
        $provincias = Provincia::all();
        $cidades = Municipio::all();
        $ibans = IbanController::getBankDetails();
        $contas = EmpresaBanco::where('empresa_id', Auth::user()->empresas->first()->id)->get();
        //response()->json($
        return view('empresa.edit', compact('empresa', 'provincias', 'cidades', 'ibans', 'contas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empresa $empresa)
    {
        try {
            $request->validate([
                // 'Logotipo' => 'nullable|file|image|max:2048',
                'Slogan' => 'nullable|string|max:100',
                'Endereco_completo' => 'required|string|max:200',
                'Provincia' => 'required|string|max:100',
                'Cidade' => 'required|string|max:100',
                'Fax' => 'nullable|string|max:100',
                'Contacto_fixo' => 'nullable|string|max:100',
            ]);
    
            $empresa->fill($request->all());
    
            $empresa->save();

            return redirect()->back()->with('success', 'Empresa actualizada com Sucesso.');

        } catch (QueryException $e) { 
            return DatabaseErrorHandler::handle($e, $request);
        } 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Empresa $empresa)
    {
        $empresa->delete();

        return redirect()->route('empresas.index')->with('success', 'Empresa excluída com sucesso.');
    }
}
