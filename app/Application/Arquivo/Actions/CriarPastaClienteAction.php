<?php

namespace App\Application\Arquivo\Actions;

use App\Application\Arquivo\DTOs\CriarPastaDTO;
use App\Application\Arquivo\Services\FileStorageService;
use App\Application\Arquivo\Services\S3PathBuilder;
use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Models\Customer;
use App\Models\Empresa;
use Illuminate\Support\Facades\Log;

final readonly class CriarPastaClienteAction
{
    public function __construct(
        private S3PathBuilder $pathBuilder,
        private FileStorageService $storage,
    ) {
    }

    public function execute(Customer $customer, Empresa $empresa): void
    {
        try {
            $this->storage->createDirectory($this->pathBuilder->folder(new CriarPastaDTO(
                empresaId: (int) $empresa->id,
                contexto: DocumentoContextoEnum::CUSTOMER,
                customerId: (int) $customer->id,
            )));
        } catch (\Throwable $e) {
            Log::warning('Falha ao criar pasta do cliente no S3.', ['customer_id' => $customer->id, 'empresa_id' => $empresa->id, 'erro' => $e->getMessage()]);
        }
    }
}
