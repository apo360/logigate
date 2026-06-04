<?php

namespace App\Application\Arquivo\Actions;

use App\Application\Arquivo\DTOs\CriarPastaDTO;
use App\Application\Arquivo\Services\FileStorageService;
use App\Application\Arquivo\Services\S3PathBuilder;
use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Models\Processo;
use Illuminate\Support\Facades\Log;

final readonly class CriarPastaProcessoAction
{
    public function __construct(
        private S3PathBuilder $pathBuilder,
        private FileStorageService $storage,
    ) {
    }

    public function execute(Processo $processo): void
    {
        try {
            $this->storage->createDirectory($this->pathBuilder->folder(new CriarPastaDTO(
                empresaId: (int) $processo->empresa_id,
                contexto: DocumentoContextoEnum::PROCESSO,
                customerId: (int) $processo->customer_id,
                processoId: (int) $processo->id,
            )));
        } catch (\Throwable $e) {
            Log::warning('Falha ao criar pasta do processo no S3.', ['processo_id' => $processo->id, 'erro' => $e->getMessage()]);
        }
    }
}
