<?php

namespace App\Application\Arquivo\Actions;

use App\Application\Arquivo\Services\FileStorageService;
use App\Models\Empresa;
use Illuminate\Support\Facades\Log;

final readonly class CriarPastaEmpresaAction
{
    public function __construct(
        private FileStorageService $storage,
    ) {
    }

    public function execute(Empresa|int $empresa): void
    {
        $empresaModel = $empresa instanceof Empresa ? $empresa : Empresa::query()->findOrFail($empresa);

        try {
            $this->storage->ensureDefaultFolders($empresaModel);
        } catch (\Throwable $e) {
            Log::warning('Falha ao criar pasta raiz da empresa no S3.', ['empresa_id' => $empresaModel->id, 'erro' => $e->getMessage()]);
        }
    }
}
