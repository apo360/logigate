<?php

namespace App\Http\Controllers;

use App\Models\DocumentosAduaneiros;
use Aws\Exception\AwsException;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\Storage;
use Aws\S3\Exception\S3Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
    }

    public function index()
    {
        $prefix = $this->tenantPrefix();

        try {
            // Security: list only objects inside authenticated tenant namespace.
            $result = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucket,
                'Prefix' => $prefix,
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
            'pasta_raiz' => 'nullable|string'
        ]);
        
        // Security: normalize user path and force tenant namespace server-side.
        $pastaRaiz = $this->normalizeTenantRelativePath($request->input('pasta_raiz'));

        foreach ($request->file('files') as $file) {
            $filename = basename($file->getClientOriginalName());
            $relativePath = trim($pastaRaiz . '/' . $filename, '/');
            $filePath = $this->buildTenantKey($relativePath);

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
                        Storage::disk('s3')->delete($this->normalizeTenantKey($fileKey));
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
                        $normalizedSource = $this->normalizeTenantKey($fileKey);
                        $fileContent = Storage::disk('s3')->get($normalizedSource);
                        $newKey = $this->buildTenantKey(
                            trim($this->normalizeTenantRelativePath($destinationFolder) . '/' . basename($normalizedSource), '/')
                        );
                        Storage::disk('s3')->put($newKey, $fileContent);
                        Storage::disk('s3')->delete($normalizedSource);
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
                        $normalizedSource = $this->normalizeTenantKey($fileKey);
                        $fileContent = Storage::disk('s3')->get($normalizedSource);
                        $newKey = $this->buildTenantKey(
                            trim($this->normalizeTenantRelativePath($destinationFolder) . '/' . basename($normalizedSource), '/')
                        );
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
        $relativePath = $this->normalizeTenantRelativePath($arquivo);
        $prefix = $this->buildTenantKey($relativePath, true);

        try {
            // Listar o conteúdo da pasta selecionada
            $result = $this->s3Client->listObjectsV2([
                'Bucket' => $this->bucket,
                'Prefix' => $prefix,
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
        $currentEmpresaId = $this->resolveEmpresaId();
        abort_if((int) $empresa_id !== (int) $currentEmpresaId, 403, 'Sem permissão para esta empresa.');

        try {
            $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                // Security: tenant root must stay under empresa/{empresa_id}/files/.
                'Key'    => $this->tenantPrefix(),
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
        $conta = $this->tenantPrefix();
        return view('arquivos.criar_pasta', compact('dir', 'conta'));
    }

    public function criarPasta(Request $request)
    {
        $request->validate([
            'nome_pasta' => 'required|string|max:255',
            'pasta_raiz' => 'required|string',
        ]);

        $nomePasta = $request->input('nome_pasta');
        $pastaRaiz = $this->normalizeTenantRelativePath($request->input('pasta_raiz'));

        // Security: folder path is normalized then rebuilt under tenant namespace.
        $caminhoCompleto = $this->buildTenantKey(trim($pastaRaiz . '/' . trim($nomePasta, '/'), '/'), true);

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
        $key = $this->normalizeTenantKey($arquivo);

        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
            ]);
    
            return redirect()->back()->with('success', 'Documento excluído com sucesso!');
        } catch (AwsException $e) {
            return redirect()->back()->with('error', 'Erro ao excluir o documento: ' . $e->getMessage());
        }
    }

    public function download($key)
    {
        $normalizedKey = $this->normalizeTenantKey($key);

        try {
            $result = $this->s3Client->getObject([
                'Bucket' => $this->bucket,
                'Key'    => $normalizedKey,
            ]);

            return response($result['Body'])
                ->header('Content-Type', $result['ContentType'])
                ->header('Content-Disposition', 'attachment; filename="' . basename($normalizedKey) . '"');
        } catch (AwsException $e) {
            return redirect()->back()->with('error', 'Erro ao baixar o documento: ' . $e->getMessage());
        }
    }

    public function visualizar($key)
    {
        $normalizedKey = $this->normalizeTenantKey($key);

        try {
            // Gerar URL assinada para o arquivo
            $cmd = $this->s3Client->getCommand('GetObject', [
                'Bucket' => $this->bucket,
                'Key'    => $normalizedKey,
            ]);
            $request = $this->s3Client->createPresignedRequest($cmd, '+20 minutes'); // URL válida por 20 minutos

            $presignedUrl = (string)$request->getUri();

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

    /**
     * Canonical tenant prefix for all file operations.
     */
    private function tenantPrefix(): string
    {
        return 'empresa/' . $this->resolveEmpresaId() . '/files/';
    }

    /**
     * Security: normalize user-provided file paths to safe tenant-relative paths.
     */
    private function normalizeTenantRelativePath(?string $path): string
    {
        $empresaId = $this->resolveEmpresaId();
        $normalized = trim((string) $path);
        $normalized = urldecode($normalized);
        $normalized = str_replace('\\', '/', $normalized);
        $normalized = preg_replace('#/+#', '/', $normalized);
        $normalized = ltrim($normalized, '/');

        // Remove already-prefixed tenant namespaces.
        $tenantPrefix = "empresa/{$empresaId}/files/";
        if (Str::startsWith($normalized, $tenantPrefix)) {
            $normalized = substr($normalized, strlen($tenantPrefix));
        }

        // Security: reject explicit references to a different tenant namespace.
        if (preg_match('#^empresa/(\d+)/files/#', $normalized, $matches) === 1 && (int) $matches[1] !== $empresaId) {
            abort(403, 'Acesso negado ao arquivo.');
        }

        // Backward compatibility for old storage prefix.
        if (preg_match('#^Despachantes/[^/]+/?#', $normalized)) {
            $normalized = preg_replace('#^Despachantes/[^/]+/?#', '', $normalized) ?? '';
        }

        if ($normalized === '' || $normalized === '/') {
            return '';
        }

        $segments = array_filter(explode('/', $normalized), static fn ($segment) => $segment !== '');

        foreach ($segments as $segment) {
            abort_if($segment === '.' || $segment === '..', 403, 'Caminho inválido.');
        }

        return implode('/', $segments);
    }

    private function buildTenantKey(string $relativePath, bool $asFolder = false): string
    {
        $relativePath = trim($relativePath, '/');
        $key = $this->tenantPrefix() . ($relativePath !== '' ? $relativePath : '');

        return $asFolder ? rtrim($key, '/') . '/' : $key;
    }

    /**
     * Security: incoming keys are always normalized back to current tenant namespace.
     */
    private function normalizeTenantKey(?string $incomingKey): string
    {
        $relativePath = $this->normalizeTenantRelativePath($incomingKey);
        $normalizedKey = $this->buildTenantKey($relativePath);

        // Defense in depth: enforce tenant prefix after normalization.
        abort_unless(Str::startsWith($normalizedKey, $this->tenantPrefix()), 403, 'Acesso negado ao arquivo.');

        return $normalizedKey;
    }
}
