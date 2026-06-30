<?php

namespace App\Application\Arquivo\Actions;

use App\Application\Arquivo\DTOs\UploadDocumentoDTO;
use App\Application\Arquivo\Repositories\DocumentoRepositoryInterface;
use App\Application\Arquivo\Services\FileAccessService;
use App\Application\Arquivo\Services\FileStorageService;
use App\Application\Arquivo\Services\S3PathBuilder;
use App\Domains\Arquivo\ValueObjects\FileMetadata;
use App\Models\ArquivoPasta;
use App\Models\Customer;
use App\Models\DocumentoArquivo;
use App\Models\Empresa;
use App\Models\Licenciamento;
use App\Models\Processo;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

final readonly class UploadDocumentoAction
{
    public function __construct(
        private DocumentoRepositoryInterface $documentos,
        private S3PathBuilder $pathBuilder,
        private FileStorageService $storage,
        private FileAccessService $access,
    ) {
    }

    public function execute(UploadDocumentoDTO $dto): DocumentoArquivo
    {
        $user = User::query()->findOrFail($dto->uploadedBy);
        $metadata = FileMetadata::fromUploadedFile($dto->file);
        $this->access->assertValidUpload($dto->file, $dto->categoria);

        [$empresaId, $customerId, $processoId, $licenciamentoId, $documentable] = $this->resolveContext($dto);
        $this->access->assertUserCanAccessEmpresa($user, $empresaId);
        Gate::forUser($user)->authorize('upload', [DocumentoArquivo::class, Empresa::query()->findOrFail($empresaId)]);

        $folder = $this->resolveFolder($dto->folderId, $empresaId);
        $path = $folder
            ? $this->pathBuilder->forFolderUpload($empresaId, $folder, $dto->categoria, $metadata->extension ?: 'bin')
            : $this->pathBuilder->forUpload($dto->contexto, $dto->categoria, $dto->entidadeId, $metadata->extension ?: 'bin', $empresaId);
        $this->storage->put($path, $dto->file);
        $storedName = basename($path->value());

        return $this->documentos->create([
            'uuid' => (string) Str::uuid(),
            'empresa_id' => $empresaId,
            'customer_id' => $customerId,
            'processo_id' => $processoId,
            'licenciamento_id' => $licenciamentoId,
            'folder_id' => $folder?->id,
            'documentable_type' => $documentable ? $documentable::class : null,
            'documentable_id' => $documentable?->getKey(),
            'contexto' => $dto->contexto->value,
            'categoria' => $dto->categoria->value,
            'visibilidade' => $dto->visibilidade->value,
            'storage_disk' => $this->storage->disk(),
            'bucket' => $this->storage->bucket(),
            'storage_key' => $path->value(),
            'stored_name' => $storedName,
            'nome_original' => $metadata->nomeOriginal,
            'mime_type' => $metadata->mimeType,
            'extension' => $metadata->extension,
            'size_bytes' => $metadata->sizeBytes,
            'sha256_hash' => $metadata->sha256Hash,
            'metadata' => [
                'client_original_name' => $metadata->nomeOriginal,
                'uploaded_context' => $dto->contexto->value,
                'uploaded_category' => $dto->categoria->value,
                'observacao' => $dto->observacao,
                'portal_visible' => $dto->portalVisible,
                'uploaded_from' => $dto->uploadedFrom,
            ],
            'is_confidential' => $dto->confidencial,
            'status' => 'activo',
            'uploaded_by' => $dto->uploadedBy,
        ]);
    }

    private function resolveContext(UploadDocumentoDTO $dto): array
    {
        return match ($dto->contexto->value) {
            'empresa', 'geral' => $this->resolveEmpresa($dto->entidadeId),
            'processo' => $this->resolveProcesso($dto->entidadeId),
            'licenciamento' => $this->resolveLicenciamento($dto->entidadeId),
            'customer' => $this->resolveCustomer($dto),
            default => throw new \InvalidArgumentException('Contexto de documento não suportado.'),
        };
    }

    private function resolveEmpresa(int $id): array
    {
        return [$id, null, null, null, null];
    }

    private function resolveProcesso(int $id): array
    {
        $processo = Processo::query()->findOrFail($id);

        return [(int) $processo->empresa_id, (int) $processo->customer_id, (int) $processo->id, null, $processo];
    }

    private function resolveLicenciamento(int $id): array
    {
        $licenciamento = Licenciamento::query()->findOrFail($id);

        return [(int) $licenciamento->empresa_id, (int) $licenciamento->cliente_id, null, (int) $licenciamento->id, $licenciamento];
    }

    private function resolveCustomer(UploadDocumentoDTO $dto): array
    {
        $customer = Customer::query()->findOrFail($dto->entidadeId);
        $empresaId = $dto->empresaId ?: (int) ($customer->empresa_id ?: $customer->empresas()->value('empresas.id'));

        if (! empty($customer->empresa_id) && (int) $customer->empresa_id === (int) $empresaId) {
            return [$empresaId, (int) $customer->id, null, null, $customer];
        }

        if ($customer->empresas()->where('empresas.id', $empresaId)->exists()) {
            return [$empresaId, (int) $customer->id, null, null, $customer];
        }

        throw new \InvalidArgumentException('Cliente não associado à empresa informada.');
    }

    private function resolveFolder(?int $folderId, int $empresaId): ?ArquivoPasta
    {
        if (! $folderId) {
            return null;
        }

        return ArquivoPasta::query()
            ->where('empresa_id', $empresaId)
            ->whereKey($folderId)
            ->firstOrFail();
    }
}
