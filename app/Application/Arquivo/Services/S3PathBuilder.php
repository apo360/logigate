<?php

namespace App\Application\Arquivo\Services;

use App\Application\Arquivo\DTOs\CriarPastaDTO;
use App\Domains\Arquivo\Enums\DocumentoCategoriaEnum;
use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Domains\Arquivo\ValueObjects\S3Path;
use App\Models\Customer;
use App\Models\Licenciamento;
use App\Models\Processo;
use Illuminate\Support\Str;

final class S3PathBuilder
{
    public function rootEmpresa(int $empresaId): S3Path
    {
        return new S3Path("despachantes/{$empresaId}/");
    }

    public function empresaLogotipo(int $empresaId, string $filename): S3Path
    {
        return new S3Path("despachantes/{$empresaId}/empresa/logotipos/{$filename}");
    }

    public function folder(CriarPastaDTO $dto): S3Path
    {
        return new S3Path(match ($dto->contexto) {
            DocumentoContextoEnum::EMPRESA => (string) $this->rootEmpresa($dto->empresaId),
            DocumentoContextoEnum::CUSTOMER => "despachantes/{$dto->empresaId}/clientes/{$dto->customerId}/",
            DocumentoContextoEnum::PROCESSO => "despachantes/{$dto->empresaId}/clientes/{$dto->customerId}/processos/{$dto->processoId}/",
            DocumentoContextoEnum::LICENCIAMENTO => "despachantes/{$dto->empresaId}/clientes/{$dto->customerId}/licenciamentos/" . now()->format('Y/m') . "/{$dto->licenciamentoId}/",
            default => "despachantes/{$dto->empresaId}/geral/",
        });
    }

    public function forUpload(
        DocumentoContextoEnum $contexto,
        DocumentoCategoriaEnum $categoria,
        int $entidadeId,
        string $extension,
    ): S3Path {
        $uuid = (string) Str::uuid();

        return new S3Path(match ($contexto) {
            DocumentoContextoEnum::PROCESSO => $this->processoPath($entidadeId, $categoria, $uuid, $extension),
            DocumentoContextoEnum::LICENCIAMENTO => $this->licenciamentoPath($entidadeId, $categoria, $uuid, $extension),
            DocumentoContextoEnum::CUSTOMER => $this->customerPath($entidadeId, $categoria, $uuid, $extension),
            default => throw new \InvalidArgumentException('Contexto de upload não suportado.'),
        });
    }

    private function processoPath(int $processoId, DocumentoCategoriaEnum $categoria, string $uuid, string $extension): string
    {
        $processo = Processo::query()->findOrFail($processoId);
        $customerId = (int) $processo->customer_id;

        return "despachantes/{$processo->empresa_id}/clientes/{$customerId}/processos/{$processo->id}/{$categoria->value}/{$uuid}.{$extension}";
    }

    private function licenciamentoPath(int $licenciamentoId, DocumentoCategoriaEnum $categoria, string $uuid, string $extension): string
    {
        $licenciamento = Licenciamento::query()->findOrFail($licenciamentoId);
        $customerId = (int) $licenciamento->cliente_id;
        $createdAt = $licenciamento->created_at ?? now();

        return "despachantes/{$licenciamento->empresa_id}/clientes/{$customerId}/licenciamentos/{$createdAt->format('Y/m')}/{$licenciamento->id}/{$categoria->value}/{$uuid}.{$extension}";
    }

    private function customerPath(int $customerId, DocumentoCategoriaEnum $categoria, string $uuid, string $extension): string
    {
        $customer = Customer::query()->findOrFail($customerId);
        $empresaId = $customer->empresa_id ?: $customer->empresas()->value('empresas.id');

        return "despachantes/{$empresaId}/clientes/{$customer->id}/geral/{$categoria->value}/{$uuid}.{$extension}";
    }
}
