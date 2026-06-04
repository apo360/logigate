<?php

namespace App\Application\Arquivo\Actions;

use App\Application\Arquivo\DTOs\UploadDocumentoDTO;
use App\Application\Arquivo\Repositories\DocumentoRepositoryInterface;
use App\Application\Arquivo\Services\FileAccessService;
use App\Application\Arquivo\Services\FileStorageService;
use App\Application\Arquivo\Services\S3PathBuilder;
use App\Domains\Arquivo\ValueObjects\FileMetadata;
use App\Models\Customer;
use App\Models\DocumentoArquivo;
use App\Models\Licenciamento;
use App\Models\Processo;
use App\Models\User;
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

        $path = $this->pathBuilder->forUpload($dto->contexto, $dto->categoria, $dto->entidadeId, $metadata->extension ?: 'bin');
        $this->storage->put($path, $dto->file);

        return $this->documentos->create([
            'uuid' => (string) Str::uuid(),
            'empresa_id' => $empresaId,
            'customer_id' => $customerId,
            'processo_id' => $processoId,
            'licenciamento_id' => $licenciamentoId,
            'documentable_type' => $documentable::class,
            'documentable_id' => $documentable->getKey(),
            'contexto' => $dto->contexto->value,
            'categoria' => $dto->categoria->value,
            'visibilidade' => $dto->visibilidade->value,
            'storage_disk' => $this->storage->disk(),
            'bucket' => $this->storage->bucket(),
            'storage_key' => $path->value(),
            'nome_original' => $metadata->nomeOriginal,
            'mime_type' => $metadata->mimeType,
            'extension' => $metadata->extension,
            'size_bytes' => $metadata->sizeBytes,
            'sha256_hash' => $metadata->sha256Hash,
            'uploaded_by' => $dto->uploadedBy,
        ]);
    }

    private function resolveContext(UploadDocumentoDTO $dto): array
    {
        return match ($dto->contexto->value) {
            'processo' => $this->resolveProcesso($dto->entidadeId),
            'licenciamento' => $this->resolveLicenciamento($dto->entidadeId),
            'customer' => $this->resolveCustomer($dto->entidadeId),
            default => throw new \InvalidArgumentException('Contexto de documento não suportado.'),
        };
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

    private function resolveCustomer(int $id): array
    {
        $customer = Customer::query()->findOrFail($id);
        $empresaId = (int) ($customer->empresa_id ?: $customer->empresas()->value('empresas.id'));

        return [$empresaId, (int) $customer->id, null, null, $customer];
    }
}
