<?php

namespace App\Http\Controllers;

use App\Models\DocumentosAduaneiros;
use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\FileService;

class ArquivoController extends AuthenticatedController
{
    /**
     * Display a listing of the resource.
     */

    public function __construct(private readonly FileService $fileService)
    {
        parent::__construct();
    }

    public function index()
    {
        try {
            $items = $this->fileService->listItems($this->resolveEmpresaId());
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
        $request->validate([
            'files' => 'required',
            'pasta_raiz' => 'nullable|string'
        ]);

        try {
            $this->fileService->uploadFiles(
                $this->resolveEmpresaId(),
                $request->file('files'),
                $request->input('pasta_raiz')
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao fazer upload: ' . $e->getMessage()], 500);
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
            if (in_array($action, ['move', 'copy'], true) && !$destinationFolder) {
                session()->flash('error', 'O destino para a ação selecionada não foi especificado!');
                return back();
            }

            $this->fileService->bulkAction($this->resolveEmpresaId(), $files, $action, $destinationFolder);
            session()->flash('success', 'Arquivos processados com sucesso!');
        } catch (S3Exception $e) {
            session()->flash('error', 'Erro ao processar os arquivos: ' . $e->getMessage());
        }

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show($arquivo)
    {
        try {
            $files = $this->fileService->listFolderContents($this->resolveEmpresaId(), $arquivo);
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
        $currentEmpresaId = $this->resolveEmpresaId();
        abort_if((int) $empresa_id !== (int) $currentEmpresaId, 403, 'Sem permissão para esta empresa.');

        try {
            $this->fileService->createMasterFolder($currentEmpresaId);

            return redirect()->back()->with('success', 'Pasta Raiz criada com sucesso.');
        } catch (AwsException $e) {
            return redirect()->back()->with('error', 'Erro ao criar a pasta Raiz: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new folder.
     */
     
    public function PastaView($dir = null){
        $conta = $this->fileService->tenantPrefix($this->resolveEmpresaId());
        return view('arquivos.criar_pasta', compact('dir', 'conta'));
    }

    public function criarPasta(Request $request)
    {
        $request->validate([
            'nome_pasta' => 'required|string|max:255',
            'pasta_raiz' => 'required|string',
        ]);

        try {
            $this->fileService->createFolder(
                $this->resolveEmpresaId(),
                $request->input('nome_pasta'),
                $request->input('pasta_raiz')
            );
    
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
            $this->fileService->deleteObject($this->resolveEmpresaId(), $arquivo);
    
            return redirect()->back()->with('success', 'Documento excluído com sucesso!');
        } catch (AwsException $e) {
            return redirect()->back()->with('error', 'Erro ao excluir o documento: ' . $e->getMessage());
        }
    }

    public function download($key)
    {
        try {
            $result = $this->fileService->downloadObject($this->resolveEmpresaId(), $key);

            return response($result['body'])
                ->header('Content-Type', $result['content_type'])
                ->header('Content-Disposition', 'attachment; filename="' . $result['filename'] . '"');
        } catch (AwsException $e) {
            return redirect()->back()->with('error', 'Erro ao baixar o documento: ' . $e->getMessage());
        }
    }

    public function visualizar($key)
    {
        try {
            $presignedUrl = $this->fileService->createPreviewUrl($this->resolveEmpresaId(), $key);

        return redirect($presignedUrl);
        } catch (AwsException $e) {
            return redirect()->back()->with('error', 'Erro ao visualizar o documento: ' . $e->getMessage());
        }
    }

    /**
     * Resolve tenant id from authenticated user or deny access.
     */
    private function resolveEmpresaId(): int
    {
        $empresaId = Auth::user()?->empresas()->value('empresas.id');
        abort_if(!$empresaId, 403, 'Nenhuma empresa associada ao usuário autenticado.');

        return (int) $empresaId;
    }
}
