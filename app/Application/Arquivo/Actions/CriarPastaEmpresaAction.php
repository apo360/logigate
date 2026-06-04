<?php

namespace App\Application\Arquivo\Actions;

use App\Application\Arquivo\DTOs\CriarPastaDTO;
use App\Application\Arquivo\Services\FileStorageService;
use App\Application\Arquivo\Services\S3PathBuilder;
use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Models\Empresa;
use Illuminate\Support\Facades\Log;

final readonly class CriarPastaEmpresaAction
{
    public function __construct(
        private S3PathBuilder $pathBuilder,
        private FileStorageService $storage,
    ) {
    }

    public function execute(Empresa|int $empresa): void
    {
        $empresaId = $empresa instanceof Empresa ? (int) $empresa->id : $empresa;

        try {
            $this->storage->createDirectory($this->pathBuilder->folder(new CriarPastaDTO(
                empresaId: $empresaId,
                contexto: DocumentoContextoEnum::EMPRESA,
            )));
        } catch (\Throwable $e) {
            Log::warning('Falha ao criar pasta raiz da empresa no S3.', ['empresa_id' => $empresaId, 'erro' => $e->getMessage()]);
        }
    }
}
