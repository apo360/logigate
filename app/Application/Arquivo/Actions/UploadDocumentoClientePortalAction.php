<?php

namespace App\Application\Arquivo\Actions;

use App\Application\Arquivo\Repositories\DocumentoRepositoryInterface;
use App\Application\Arquivo\Services\FileAccessService;
use App\Application\Arquivo\Services\FileStorageService;
use App\Application\Arquivo\Services\S3PathBuilder;
use App\Domains\Arquivo\Enums\DocumentoCategoriaEnum;
use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Domains\Arquivo\Enums\DocumentoVisibilidadeEnum;
use App\Domains\Arquivo\ValueObjects\FileMetadata;
use App\Models\ClientePortal;
use App\Models\Customer;
use App\Models\DocumentoArquivo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

final readonly class UploadDocumentoClientePortalAction
{
    public function __construct(
        private DocumentoRepositoryInterface $documentos,
        private S3PathBuilder $pathBuilder,
        private FileStorageService $storage,
        private FileAccessService $access,
    ) {
    }

    public function execute(ClientePortal $portal, UploadedFile $file, DocumentoCategoriaEnum $categoria, ?string $observacao = null): DocumentoArquivo
    {
        /** @var Customer $customer */
        $customer = $portal->customer()->firstOrFail();
        $empresaId = (int) $portal->empresa_id;

        Gate::forUser($portal)->authorize('uploadPortal', [DocumentoArquivo::class, $customer]);

        $metadata = FileMetadata::fromUploadedFile($file);
        $this->access->assertValidUpload($file, $categoria);

        $path = $this->pathBuilder->forUpload(DocumentoContextoEnum::CUSTOMER, $categoria, (int) $customer->id, $metadata->extension ?: 'bin', $empresaId);
        $this->storage->put($path, $file);

        return $this->documentos->create([
            'uuid' => (string) Str::uuid(),
            'empresa_id' => $empresaId,
            'customer_id' => (int) $customer->id,
            'processo_id' => null,
            'licenciamento_id' => null,
            'documentable_type' => Customer::class,
            'documentable_id' => (int) $customer->id,
            'contexto' => DocumentoContextoEnum::CUSTOMER->value,
            'categoria' => $categoria->value,
            'visibilidade' => DocumentoVisibilidadeEnum::CLIENTE->value,
            'storage_disk' => $this->storage->disk(),
            'bucket' => $this->storage->bucket(),
            'storage_key' => $path->value(),
            'stored_name' => basename($path->value()),
            'nome_original' => $metadata->nomeOriginal,
            'mime_type' => $metadata->mimeType,
            'extension' => $metadata->extension,
            'size_bytes' => $metadata->sizeBytes,
            'sha256_hash' => $metadata->sha256Hash,
            'metadata' => [
                'client_original_name' => $metadata->nomeOriginal,
                'uploaded_context' => DocumentoContextoEnum::CUSTOMER->value,
                'uploaded_category' => $categoria->value,
                'observacao' => $observacao,
                'portal_visible' => true,
                'uploaded_from' => 'cliente_portal',
                'cliente_portal_id' => (int) $portal->id,
            ],
            'is_confidential' => false,
            'status' => 'pending',
            'uploaded_by' => null,
        ]);
    }
}
