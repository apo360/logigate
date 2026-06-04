<?php

namespace App\Application\Arquivo\Actions;

use App\Application\Arquivo\DTOs\CriarPastaDTO;
use App\Application\Arquivo\Services\FileStorageService;
use App\Application\Arquivo\Services\S3PathBuilder;
use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Models\Licenciamento;
use Illuminate\Support\Facades\Log;

final readonly class CriarPastaLicenciamentoAction
{
    public function __construct(
        private S3PathBuilder $pathBuilder,
        private FileStorageService $storage,
    ) {
    }

    public function execute(Licenciamento $licenciamento): void
    {
        try {
            $this->storage->createDirectory($this->pathBuilder->folder(new CriarPastaDTO(
                empresaId: (int) $licenciamento->empresa_id,
                contexto: DocumentoContextoEnum::LICENCIAMENTO,
                customerId: (int) $licenciamento->cliente_id,
                licenciamentoId: (int) $licenciamento->id,
            )));
        } catch (\Throwable $e) {
            Log::warning('Falha ao criar pasta do licenciamento no S3.', ['licenciamento_id' => $licenciamento->id, 'erro' => $e->getMessage()]);
        }
    }
}
