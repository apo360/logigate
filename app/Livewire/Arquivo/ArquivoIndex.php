<?php

namespace App\Livewire\Arquivo;

use App\Application\Arquivo\Actions\CriarArquivoPastaAction;
use App\Application\Arquivo\Actions\DeleteDocumentoAction;
use App\Application\Arquivo\Actions\GerarUrlAssinadaAction;
use App\Application\Arquivo\Actions\SincronizarPastasEmpresaAction;
use App\Application\Arquivo\Actions\UploadDocumentoAction;
use App\Application\Arquivo\DTOs\UploadDocumentoDTO;
use App\Application\Arquivo\Repositories\DocumentoRepositoryInterface;
use App\Application\Arquivo\Services\ArquivoStorageService;
use App\Domains\Arquivo\Enums\DocumentoCategoriaEnum;
use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Models\ArquivoPasta;
use App\Models\Empresa;
use App\Models\DocumentoArquivo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class ArquivoIndex extends Component
{
    use WithFileUploads;

    public ?Empresa $empresa = null;

    public bool $schemaReady = true;

    public bool $foldersReady = true;

    public bool $folderColumnReady = true;

    #[Url(as: 'pasta')]
    public ?int $currentFolderId = null;

    public string $search = '';

    public string $categoria = '';

    public string $contexto = '';

    public string $tipo = '';

    public string $categoriaUpload = 'documentos';

    public string $novaPastaNome = '';

    public string $novaPastaTipo = 'custom';

    public array $files = [];

    public function mount(): void
    {
        $this->empresa = Auth::user()?->empresaAtiva();
        abort_unless($this->empresa, 403);
        Gate::forUser(Auth::user())->authorize('viewAny', [DocumentoArquivo::class, $this->empresa]);

        $this->schemaReady = Schema::hasTable('documentos_arquivos');
        $this->foldersReady = Schema::hasTable('arquivo_pastas');
        $this->folderColumnReady = $this->schemaReady && Schema::hasColumn('documentos_arquivos', 'folder_id');

        if ($this->foldersReady) {
            app(SincronizarPastasEmpresaAction::class)->execute($this->empresa);
            $this->resolveCurrentFolder();
        }
    }

    public function upload(UploadDocumentoAction $action, ArquivoStorageService $storage): void
    {
        abort_unless($this->empresa, 403);

        if (! $this->schemaReady) {
            $this->addError('files', 'A tabela documentos_arquivos ainda não existe. Execute a migration aprovada.');
            return;
        }

        if (! $storage->isConfigured()) {
            $this->addError('files', 'S3 não configurado. O upload cloud está desactivado até configuração administrativa.');
            return;
        }

        Gate::forUser(Auth::user())->authorize('upload', [DocumentoArquivo::class, $this->empresa]);

        $this->validate([
            'categoriaUpload' => ['required', 'string'],
            'files' => ['required', 'array', 'min:1', 'max:8'],
            'files.*' => ['file', 'max:10240'],
        ]);

        try {
            foreach ($this->files as $file) {
                $action->execute(new UploadDocumentoDTO(
                    file: $file,
                    contexto: DocumentoContextoEnum::EMPRESA,
                    categoria: DocumentoCategoriaEnum::from($this->categoriaUpload),
                    entidadeId: (int) $this->empresa->id,
                    uploadedBy: (int) Auth::id(),
                    folderId: $this->folderIdForUpload(),
                ));
            }
        } catch (\Throwable $exception) {
            $this->addError('files', 'Erro ao enviar documento: ' . $exception->getMessage());
            return;
        }

        $this->reset('files');
        $this->dispatch('toast', type: 'success', message: 'Documento(s) enviado(s) com sucesso.');
    }

    public function criarPasta(CriarArquivoPastaAction $action): void
    {
        abort_unless($this->empresa, 403);

        if (! $this->foldersReady) {
            $this->addError('novaPastaNome', 'A tabela arquivo_pastas ainda não existe. Execute a migration aprovada.');
            return;
        }

        if (! $this->canCreateFolder()) {
            $this->addError('novaPastaNome', 'Sem permissão para criar pastas.');
            return;
        }

        $this->validate([
            'novaPastaNome' => ['required', 'string', 'min:2', 'max:120'],
            'novaPastaTipo' => ['required', 'string', 'max:40'],
        ]);

        try {
            $action->execute(
                $this->empresa,
                Auth::user(),
                $this->novaPastaNome,
                $this->currentFolder()?->id ?? $this->rootFolder()?->id,
                $this->novaPastaTipo,
            );
        } catch (\Throwable $exception) {
            $this->addError('novaPastaNome', $exception->getMessage());
            return;
        }

        $this->reset(['novaPastaNome']);
        $this->novaPastaTipo = 'custom';
        $this->dispatch('toast', type: 'success', message: 'Pasta criada com sucesso.');
    }

    public function abrirPasta(int $folderId): void
    {
        abort_unless($this->foldersReady && $this->empresa, 404);

        ArquivoPasta::query()
            ->where('empresa_id', $this->empresa->id)
            ->whereKey($folderId)
            ->firstOrFail();

        $this->currentFolderId = $folderId;
        $this->resetPageLikeFilters();
    }

    public function irParaRaiz(): void
    {
        $this->currentFolderId = null;
        $this->resetPageLikeFilters();
    }

    public function download(int $documentoId, GerarUrlAssinadaAction $action)
    {
        return redirect()->away($action->execute($documentoId, Auth::user(), 5));
    }

    public function remover(int $documentoId, DeleteDocumentoAction $action): void
    {
        $action->execute($documentoId, Auth::user());
        $this->dispatch('toast', type: 'success', message: 'Documento removido com sucesso.');
    }

    public function documentos(DocumentoRepositoryInterface $documentos)
    {
        if (! $this->empresa || ! $this->schemaReady) {
            return collect();
        }

        return $documentos->listForEmpresa((int) $this->empresa->id, [
            'search' => trim($this->search),
            'categoria' => $this->categoria ?: null,
            'contexto' => $this->contexto ?: null,
            'tipo' => $this->tipo ?: null,
            ...($this->currentFolderId && $this->folderColumnReady ? ['folder_id' => $this->currentFolderId] : []),
        ]);
    }

    public function storageStatus(ArquivoStorageService $storage)
    {
        return $storage->configurationStatus($this->empresa);
    }

    public function render(DocumentoRepositoryInterface $documentos, ArquivoStorageService $storage)
    {
        $lista = $this->documentos($documentos);
        $currentFolder = $this->currentFolder();
        $pastas = $this->pastasDaPastaActual();

        return view('livewire.arquivo.arquivo-index', [
            'documentos' => $lista,
            'pastas' => $pastas,
            'currentFolder' => $currentFolder,
            'breadcrumb' => $this->breadcrumb($currentFolder),
            'categorias' => DocumentoCategoriaEnum::cases(),
            'contextos' => DocumentoContextoEnum::cases(),
            'tipos' => $lista->pluck('extension')->filter()->unique()->sort()->values(),
            'status' => $this->storageStatus($storage),
            'totalSize' => $lista->sum('size_bytes'),
            'totalDocumentos' => $this->totalDocumentos(),
            'totalPastas' => $this->totalPastas(),
            'ultimosUploads' => $lista->take(5),
            'porModulo' => $lista->groupBy('contexto')->map->count(),
            'canUpload' => Gate::forUser(Auth::user())->allows('upload', [DocumentoArquivo::class, $this->empresa]),
            'canCreateFolder' => $this->canCreateFolder(),
        ]);
    }

    private function rootFolder(): ?ArquivoPasta
    {
        if (! $this->foldersReady || ! $this->empresa) {
            return null;
        }

        return ArquivoPasta::query()
            ->where('empresa_id', $this->empresa->id)
            ->where('path', '')
            ->first();
    }

    private function currentFolder(): ?ArquivoPasta
    {
        if (! $this->foldersReady || ! $this->empresa || ! $this->currentFolderId) {
            return null;
        }

        return ArquivoPasta::query()
            ->where('empresa_id', $this->empresa->id)
            ->whereKey($this->currentFolderId)
            ->firstOrFail();
    }

    private function resolveCurrentFolder(): void
    {
        if (! $this->currentFolderId) {
            return;
        }

        $this->currentFolder();
    }

    private function pastasDaPastaActual()
    {
        if (! $this->foldersReady || ! $this->empresa) {
            return collect();
        }

        $parentId = $this->currentFolder()?->id ?? $this->rootFolder()?->id;

        $query = ArquivoPasta::query()
            ->where('empresa_id', $this->empresa->id)
            ->where('parent_id', $parentId)
            ->withCount('children')
            ->orderByDesc('is_system')
            ->orderBy('name');

        if ($this->folderColumnReady) {
            $query->withCount('documentos');
        }

        return $query->get()->map(function (ArquivoPasta $pasta): ArquivoPasta {
            if (! array_key_exists('documentos_count', $pasta->getAttributes())) {
                $pasta->setAttribute('documentos_count', 0);
            }

            return $pasta;
        });
    }

    private function breadcrumb(?ArquivoPasta $currentFolder): array
    {
        $items = [['id' => null, 'name' => 'Arquivo']];

        if (! $currentFolder) {
            return $items;
        }

        $folders = collect();
        $folder = $currentFolder;

        while ($folder) {
            if ($folder->path !== '') {
                $folders->prepend(['id' => $folder->id, 'name' => $folder->name]);
            }

            $folder = $folder->parent;
        }

        return array_merge($items, $folders->all());
    }

    private function folderIdForUpload(): ?int
    {
        if (! $this->foldersReady || ! $this->folderColumnReady) {
            return null;
        }

        if ($this->currentFolderId) {
            return $this->currentFolder()?->id;
        }

        return ArquivoPasta::query()
            ->where('empresa_id', $this->empresa?->id)
            ->where('path', 'documentos')
            ->value('id');
    }

    private function canCreateFolder(): bool
    {
        if (! $this->empresa) {
            return false;
        }

        return Gate::forUser(Auth::user())->allows('manage', [DocumentoArquivo::class, $this->empresa])
            || Gate::forUser(Auth::user())->allows('upload', [DocumentoArquivo::class, $this->empresa]);
    }

    private function totalDocumentos(): int
    {
        if (! $this->empresa || ! $this->schemaReady) {
            return 0;
        }

        return DocumentoArquivo::query()
            ->where('empresa_id', $this->empresa->id)
            ->count();
    }

    private function totalPastas(): int
    {
        if (! $this->empresa || ! $this->foldersReady) {
            return 0;
        }

        return ArquivoPasta::query()
            ->where('empresa_id', $this->empresa->id)
            ->count();
    }

    private function resetPageLikeFilters(): void
    {
        $this->reset(['search', 'categoria', 'contexto', 'tipo']);
    }
}
