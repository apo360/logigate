<?php

namespace App\Http\Controllers;

use App\Helpers\DatabaseErrorHandler;
use App\Http\Requests\DocumentoAduRequest;
use App\Models\DocumentosAduaneiros;
use App\Models\EmpresaUser;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use Aws\S3\Exception\S3Exception;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ArquivoController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    protected $bucket = 'logigate-arquivos-aduaneiro';

    protected $s3Client;

    public function __construct()
    {
        // Inicializar o cliente S3
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => 'us-east-1', // A região do seu bucket S3
        ]);
    }

    public function index()
    {
        // Obter o nome do usuário autenticado
        $username = Auth::user()->empresas->first()->conta;

        try {
            // Listar as pastas e sub-pastas dentro de 'Despachantes/{empresaId}'
            $result = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucket,
                'Prefix' => "Despachantes/$username/",
                'Delimiter' => '/',
            ]);

            // Obter pastas e sub-pastas
            $folders = $result['CommonPrefixes'] ?? [];
            $files = $result['Contents'] ?? [];

            // Formatando dados para a view
            $items = array_map(function($folder) {
                return [
                    'name' => basename($folder['Prefix']),
                    'path' => $folder['Prefix'],
                    'type' => 'folder',
                ];
            }, $folders);

            $items = array_merge($items, array_map(function($file) {
                return [
                    'name' => basename($file['Key']),
                    'path' => $file['Key'],
                    'size' => $file['Size'],
                    'last_modified' => $file['LastModified'],
                    'type' => 'file',
                ];
            }, $files));

            // Retornar a view com os itens
            return view('arquivos.index', compact('items'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao listar as pastas: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($dir = null)
    {
        return view('arquivos.upload', compact('dir'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validação
        $request->validate([
            'files' => 'required',
            'pasta_raiz' => 'required'
        ]);
        
        $pastaRaiz = $request->input('pasta_raiz');

        foreach ($request->file('files') as $file) {
            $filePath = 'Despachantes/' . $pastaRaiz . '/' . $file->getClientOriginalName();

            try {
                $this->s3Client->putObject([
                    'Bucket' => $this->bucket,
                    'Key'    => $filePath,
                    'SourceFile' => $file->getPathname(),
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Erro ao fazer upload: ' . $e->getMessage()], 500);
            }
        }

        return response()->json(['success' => 'Arquivos carregados com sucesso!'], 200);
    }

    /**
     * Processa as ações em lote (excluir, mover ou copiar arquivos).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkActions(Request $request)
    {
        // Valida os arquivos selecionados
        $validated = $request->validate([
            'files' => 'required|array|min:1',
            'action' => 'required|string|in:delete,move,copy',
            'destination_folder' => 'nullable|string', // Necessário para mover ou copiar
        ]);

        // Obtém a lista de arquivos selecionados e a ação
        $files = $validated['files'];
        $action = $validated['action'];
        $destinationFolder = $validated['destination_folder'] ?? null;

        try {
            switch ($action) {
                case 'delete':
                    // Excluir os arquivos selecionados
                    foreach ($files as $fileKey) {
                        Storage::disk('s3')->delete($fileKey);
                    }
                    session()->flash('success', 'Arquivos excluídos com sucesso!');
                    break;

                case 'move':
                    if (!$destinationFolder) {
                        session()->flash('error', 'O destino para mover os arquivos não foi especificado!');
                        return back();
                    }
                    // Mover os arquivos selecionados para a pasta de destino
                    foreach ($files as $fileKey) {
                        $fileContent = Storage::disk('s3')->get($fileKey);
                        $newKey = $destinationFolder . '/' . basename($fileKey);
                        Storage::disk('s3')->put($newKey, $fileContent);
                        Storage::disk('s3')->delete($fileKey);
                    }
                    session()->flash('success', 'Arquivos movidos com sucesso!');
                    break;

                case 'copy':
                    if (!$destinationFolder) {
                        session()->flash('error', 'O destino para copiar os arquivos não foi especificado!');
                        return back();
                    }
                    // Copiar os arquivos selecionados para a pasta de destino
                    foreach ($files as $fileKey) {
                        $fileContent = Storage::disk('s3')->get($fileKey);
                        $newKey = $destinationFolder . '/' . basename($fileKey);
                        Storage::disk('s3')->put($newKey, $fileContent);
                    }
                    session()->flash('success', 'Arquivos copiados com sucesso!');
                    break;

                default:
                    session()->flash('error', 'Ação inválida.');
                    return back();
            }
        } catch (S3Exception $e) {
            // Captura exceções do S3
            session()->flash('error', 'Erro ao processar os arquivos: ' . $e->getMessage());
        }

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show($arquivo)
    {
        // Obter o nome do usuário autenticado
        $username = Auth::user()->empresas->first()->conta;

        try {
            // Listar o conteúdo da pasta selecionada
            $result = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucket,
                'Prefix' => "Despachantes/$username/$arquivo/",
            ]);

            // Obter arquivos e sub-pastas dentro da pasta selecionada
            $files = $result->get('Contents');
            return view('arquivos.show', compact('files', 'arquivo'));
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao listar os arquivos: ' . $e->getMessage());
        }
    }

    /**
     * Criar a Pasta Principal do Cliente na Pasta Despachantes.
     */
    public function createMasterFolder($empresa_id)
    {
        // Obter a conta da empresa
        $empresa = EmpresaUser::where('empresa_id', $empresa_id)->first();
        $conta = $empresa->conta;

        try {
            $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => "Despachantes/$conta/",
                'Body'   => "", // Corpo vazio para simular uma pasta
            ]);

            return redirect()->back()->with('success', 'Pasta Raiz criada com sucesso.');
        } catch (AwsException $e) {
            return redirect()->back()->with('error', 'Erro ao criar a pasta Raiz: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new folder.
     */
     
    public function PastaView($dir = null){
        return view('arquivos.criar_pasta', compact('dir'));
    }

    public function criarPasta(Request $request)
    {
        $request->validate([
            'nome_pasta' => 'required|string|max:255',
            'pasta_raiz' => 'required|string',
        ]);

        $nomePasta = $request->input('nome_pasta');
        $pastaRaiz = $request->input('pasta_raiz');

        // Criar o caminho completo da pasta, incluindo a raiz e o nome da pasta
        $caminhoCompleto = 'Despachantes/' . rtrim($pastaRaiz, '/') . '/' . rtrim($nomePasta, '/') . '/';

        try {
            // Criando a "pasta" no S3
            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $caminhoCompleto, // Garante que termina com '/'
                'Body'   => "", // Corpo vazio para simular uma pasta
            ]);
    
            return redirect()->back()->with('success', 'Pasta criada com sucesso.');
        } catch (AwsException $e) {
            // Tratamento de erro detalhado
            $errorMessage = $e->getAwsErrorMessage() ?: 'Erro desconhecido ao tentar criar a pasta.';
            return redirect()->back()->with('error', 'Erro ao criar a pasta: ' . $errorMessage);
        }
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DocumentosAduaneiros $documentosAduaneiros)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentosAduaneiros $documentosAduaneiros)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($arquivo)
    {
    
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $arquivo,
            ]);
    
            return redirect()->back()->with('success', 'Documento excluído com sucesso!');
        } catch (AwsException $e) {
            return redirect()->back()->with('error', 'Erro ao excluir o documento: ' . $e->getMessage());
        }
    }

    public function download($key)
    {
        try {
            $result = $this->s3Client->getObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
            ]);

            return response($result['Body'])
                ->header('Content-Type', $result['ContentType'])
                ->header('Content-Disposition', 'attachment; filename="' . basename($key) . '"');
        } catch (AwsException $e) {
            return redirect()->back()->with('error', 'Erro ao baixar o documento: ' . $e->getMessage());
        }
    }

    public function visualizar($key)
    {

        try {
            // Gerar URL assinada para o arquivo
            $cmd = $this->s3Client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key'    => $key,
            ]);
            $request = $this->s3Client->createPresignedRequest($cmd, '+20 minutes'); // URL válida por 20 minutos

            $presignedUrl = (string)$request->getUri();

        return redirect($presignedUrl);
        } catch (AwsException $e) {
            return redirect()->back()->with('error', 'Erro ao visualizar o documento: ' . $e->getMessage());
        }
    }

}
