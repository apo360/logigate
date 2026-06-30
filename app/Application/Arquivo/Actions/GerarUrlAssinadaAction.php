<?php

namespace App\Application\Arquivo\Actions;

use App\Application\Arquivo\Repositories\DocumentoRepositoryInterface;
use App\Application\Arquivo\Services\FileAccessService;
use App\Application\Arquivo\Services\FileStorageService;
use App\Domains\Arquivo\ValueObjects\S3Path;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final readonly class GerarUrlAssinadaAction
{
    public function __construct(
        private DocumentoRepositoryInterface $documentos,
        private FileAccessService $access,
        private FileStorageService $storage,
    ) {
    }

    public function execute(int $documentoId, User $user, int $minutes = 5): string
    {
        $documento = $this->documentos->findOrFail($documentoId);
        $this->access->assertCanView($user, $documento);
        Gate::forUser($user)->authorize('download', $documento);

        return $this->storage->temporaryUrl(new S3Path($documento->storage_key), $minutes);
    }
}
