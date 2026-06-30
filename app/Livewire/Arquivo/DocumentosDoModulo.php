<?php

namespace App\Livewire\Arquivo;

use App\Application\Arquivo\Actions\DeleteDocumentoAction;
use App\Application\Arquivo\Actions\GerarUrlAssinadaAction;
use App\Application\Arquivo\Actions\ListarDocumentosAction;
use App\Application\Arquivo\Actions\UploadDocumentoAction;
use App\Application\Arquivo\DTOs\UploadDocumentoDTO;
use App\Application\Arquivo\Services\ArquivoStorageService;
use App\Domains\Arquivo\Enums\DocumentoCategoriaEnum;
use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Models\Customer;
use App\Models\DocumentoArquivo;
use App\Models\Licenciamento;
use App\Models\Processo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class DocumentosDoModulo extends Component
{
    use WithFileUploads;

    private const CONTEXT_MODELS = [
        'customer' => Customer::class,
        'processo' => Processo::class,
        'licenciamento' => Licenciamento::class,
    ];

    #[Locked]
    public string $contexto;

    #[Locked]
    public int $entidadeId;

    public string $search = '';

    public string $categoria = '';

    public string $tipo = '';

    public string $categoriaUpload = 'documentos';

    public string $observacao = '';

    public bool $confidencial = false;

    public bool $portalVisible = false;

    public array $files = [];

    public bool $schemaReady = true;

    public function mount(string $contexto, int $entidadeId): void
    {
        abort_unless(array_key_exists($contexto, self::CONTEXT_MODELS), 404);

        $this->contexto = $contexto;
        $this->entidadeId = $entidadeId;
        $this->schemaReady = Schema::hasTable('documentos_arquivos');

        $this->resolveDocumentable();
    }

    public function upload(UploadDocumentoAction $action, ArquivoStorageService $storage): void
    {
        $model = $this->resolveDocumentable();
        $empresaId = $this->empresaIdFrom($model);

        if (! $this->schemaReady) {
            $this->addError('files', 'A tabela documentos_arquivos ainda não existe. Execute a migration aprovada.');
            return;
        }

        if (! $this->canUpload()) {
            $this->addError('files', 'Sem permissão para enviar documentos neste módulo.');
            return;
        }

        if (! $storage->isConfigured()) {
            $this->addError('files', 'S3 não configurado. O upload cloud está desactivado até configuração administrativa.');
            return;
        }

        $this->validate([
            'categoriaUpload' => ['required', 'string'],
            'observacao' => ['nullable', 'string', 'max:500'],
            'confidencial' => ['boolean'],
            'portalVisible' => ['boolean'],
            'files' => ['required', 'array', 'min:1', 'max:8'],
            'files.*' => ['file', 'max:10240'],
        ]);

        try {
            foreach ($this->files as $file) {
                $action->execute(new UploadDocumentoDTO(
                    file: $file,
                    contexto: DocumentoContextoEnum::from($this->contexto),
                    categoria: DocumentoCategoriaEnum::from($this->categoriaUpload),
                    entidadeId: (int) $model->getKey(),
                    uploadedBy: (int) Auth::id(),
                    observacao: trim($this->observacao) !== '' ? trim($this->observacao) : null,
                    confidencial: $this->confidencial,
                    portalVisible: $this->contexto === 'customer' && $this->portalVisible,
                    uploadedFrom: 'admin',
                    empresaId: $empresaId,
                ));
            }
        } catch (\Throwable $exception) {
            $this->addError('files', 'Erro ao enviar documento: ' . $exception->getMessage());
            return;
        }

        $this->reset(['files', 'observacao', 'confidencial', 'portalVisible']);
        $this->categoriaUpload = 'documentos';
        $this->dispatch('toast', type: 'success', message: 'Documento(s) anexado(s) com sucesso.');
    }

    public function download(int $documentoId, GerarUrlAssinadaAction $action)
    {
        $this->documentoDoModuloOrFail($documentoId);

        return redirect()->away($action->execute($documentoId, Auth::user(), 5));
    }

    public function remover(int $documentoId, DeleteDocumentoAction $action): void
    {
        $this->documentoDoModuloOrFail($documentoId);
        $action->execute($documentoId, Auth::user());
        $this->dispatch('toast', type: 'success', message: 'Documento removido com sucesso.');
    }

    public function documentos(ListarDocumentosAction $action): Collection
    {
        if (! $this->schemaReady || ! $this->canView()) {
            return collect();
        }

        $model = $this->resolveDocumentable();

        return $action->execute(
            DocumentoContextoEnum::from($this->contexto),
            (int) $model->getKey(),
            $this->empresaIdFrom($model),
            Auth::user(),
            [
                'search' => trim($this->search),
                'categoria' => $this->categoria ?: null,
                'tipo' => $this->tipo ?: null,
            ],
        );
    }

    public function render(ListarDocumentosAction $listarDocumentos, ArquivoStorageService $storage)
    {
        $documentos = $this->documentos($listarDocumentos);

        return view('livewire.arquivo.documentos-do-modulo', [
            'documentos' => $documentos,
            'categoriasUpload' => $this->categoriasUpload(),
            'categoriasFiltro' => $documentos->pluck('categoria')->filter()->unique()->sort()->values(),
            'tipos' => $documentos->pluck('extension')->filter()->unique()->sort()->values(),
            'status' => $storage->configurationStatus(Auth::user()?->empresaAtiva()),
            'canView' => $this->canView(),
            'canUpload' => $this->canUpload(),
            'titulo' => $this->titulo(),
            'subtitulo' => $this->subtitulo(),
        ]);
    }

    private function resolveDocumentable(): Model
    {
        $class = self::CONTEXT_MODELS[$this->contexto] ?? null;
        abort_unless($class, 404);

        /** @var Model $model */
        $model = $class::query()->findOrFail($this->entidadeId);
        $empresaId = $this->empresaIdFrom($model);
        $activeEmpresaId = (int) (Auth::user()?->empresaAtiva()?->id ?? 0);

        abort_unless($activeEmpresaId > 0 && $empresaId === $activeEmpresaId, 404);
        abort_unless(Gate::forUser(Auth::user())->allows('view', $model), 403);

        return $model;
    }

    private function empresaIdFrom(Model $model): int
    {
        if ($model instanceof Customer) {
            $activeEmpresaId = (int) (Auth::user()?->empresaAtiva()?->id ?? 0);

            if ($activeEmpresaId > 0) {
                $directMatch = (int) ($model->getAttribute('empresa_id') ?? 0) === $activeEmpresaId;
                $pivotMatch = $model->empresas()->where('empresas.id', $activeEmpresaId)->exists();

                if ($directMatch || $pivotMatch) {
                    return $activeEmpresaId;
                }
            }
        }

        return (int) $model->getAttribute('empresa_id');
    }

    private function documentoDoModuloOrFail(int $documentoId): DocumentoArquivo
    {
        $model = $this->resolveDocumentable();

        return DocumentoArquivo::query()
            ->whereKey($documentoId)
            ->where('empresa_id', $this->empresaIdFrom($model))
            ->where('contexto', $this->contexto)
            ->where($this->contextColumn(), $model->getKey())
            ->where('documentable_type', $model::class)
            ->where('documentable_id', $model->getKey())
            ->firstOrFail();
    }

    private function canView(): bool
    {
        $model = $this->resolveDocumentable();

        return Gate::forUser(Auth::user())->allows('viewAny', [DocumentoArquivo::class, Auth::user()?->empresaAtiva()])
            && $this->empresaIdFrom($model) === (int) Auth::user()?->empresaAtiva()?->id;
    }

    private function canUpload(): bool
    {
        $model = $this->resolveDocumentable();

        return Gate::forUser(Auth::user())->allows('upload', [DocumentoArquivo::class, Auth::user()?->empresaAtiva()])
            && $this->empresaIdFrom($model) === (int) Auth::user()?->empresaAtiva()?->id;
    }

    private function categoriasUpload(): array
    {
        return match ($this->contexto) {
            'processo' => [
                DocumentoCategoriaEnum::DOCUMENTOS->value => 'Geral',
                DocumentoCategoriaEnum::PROFORMAS->value => 'Factura/Proforma',
                DocumentoCategoriaEnum::MERCADORIAS->value => 'BL ou Packing List',
                DocumentoCategoriaEnum::XML->value => 'DU/XML',
                DocumentoCategoriaEnum::DESPESAS->value => 'Nota de liquidação',
                DocumentoCategoriaEnum::COMPROVATIVOS->value => 'Comprovativo',
                DocumentoCategoriaEnum::OUTROS->value => 'Outro',
            ],
            'licenciamento' => [
                DocumentoCategoriaEnum::DOCUMENTOS->value => 'Geral',
                DocumentoCategoriaEnum::PROFORMAS->value => 'Pedido/Factura',
                DocumentoCategoriaEnum::CONTRATOS->value => 'Documento de suporte',
                DocumentoCategoriaEnum::COMPROVATIVOS->value => 'Comprovativo',
                DocumentoCategoriaEnum::RELATORIOS->value => 'Parecer',
                DocumentoCategoriaEnum::OUTROS->value => 'Outro',
            ],
            'customer' => [
                DocumentoCategoriaEnum::DOCUMENTOS->value => 'Geral',
                DocumentoCategoriaEnum::DOCUMENTOS_IDENTIFICACAO->value => 'BI / Identificação',
                DocumentoCategoriaEnum::CONTRATOS->value => 'Procuração / Contrato',
                DocumentoCategoriaEnum::COMPROVATIVOS->value => 'Comprovativo',
                DocumentoCategoriaEnum::RECIBOS->value => 'Documento fiscal',
                DocumentoCategoriaEnum::XML->value => 'Documento aduaneiro / XML',
                DocumentoCategoriaEnum::OUTROS->value => 'Outro',
            ],
            default => [],
        };
    }

    private function contextColumn(): string
    {
        return match ($this->contexto) {
            'processo' => 'processo_id',
            'licenciamento' => 'licenciamento_id',
            'customer' => 'customer_id',
            default => throw new \InvalidArgumentException('Contexto de documento não suportado.'),
        };
    }

    private function titulo(): string
    {
        return match ($this->contexto) {
            'processo' => 'Documentos do Processo',
            'licenciamento' => 'Documentos do Licenciamento',
            'customer' => 'Documentos do Cliente',
            default => 'Documentos',
        };
    }

    private function subtitulo(): string
    {
        return match ($this->contexto) {
            'processo' => 'Anexos privados associados a este processo.',
            'licenciamento' => 'Anexos privados associados a este licenciamento.',
            'customer' => 'Anexos privados associados a este cliente, com controlo de visibilidade no portal.',
            default => 'Anexos privados associados a este registo.',
        };
    }
}
