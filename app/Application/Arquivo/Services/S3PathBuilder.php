<?php

namespace App\Application\Arquivo\Services;

use App\Application\Arquivo\DTOs\CriarPastaDTO;
use App\Domains\Arquivo\Enums\DocumentoCategoriaEnum;
use App\Domains\Arquivo\Enums\DocumentoContextoEnum;
use App\Domains\Arquivo\ValueObjects\S3Path;
use App\Models\ArquivoPasta;
use App\Models\Customer;
use App\Models\Empresa;
use App\Models\Licenciamento;
use App\Models\Processo;
use Illuminate\Support\Str;

final class S3PathBuilder
{
    public function rootEmpresa(Empresa|int $empresa): S3Path
    {
        $empresaModel = $empresa instanceof Empresa ? $empresa : Empresa::query()->findOrFail($empresa);

        return new S3Path('logigate/empresas/' . $this->empresaKey($empresaModel) . '/');
    }

    public function empresaLogotipo(Empresa|int $empresa, string $filename): S3Path
    {
        $root = rtrim($this->rootEmpresa($empresa)->value(), '/');

        return new S3Path("{$root}/documentos/empresa/logotipos/{$filename}");
    }

    public function folder(CriarPastaDTO $dto): S3Path
    {
        return new S3Path(match ($dto->contexto) {
            DocumentoContextoEnum::EMPRESA => (string) $this->rootEmpresa($dto->empresaId),
            DocumentoContextoEnum::GERAL => rtrim($this->rootEmpresa($dto->empresaId)->value(), '/') . '/documentos/geral/',
            DocumentoContextoEnum::CUSTOMER => rtrim($this->rootEmpresa($dto->empresaId)->value(), '/') . "/clientes/{$dto->customerId}/",
            DocumentoContextoEnum::PROCESSO => rtrim($this->rootEmpresa($dto->empresaId)->value(), '/') . "/processos/{$dto->processoId}/",
            DocumentoContextoEnum::LICENCIAMENTO => rtrim($this->rootEmpresa($dto->empresaId)->value(), '/') . '/licenciamentos/' . now()->format('Y/m') . "/{$dto->licenciamentoId}/",
            default => rtrim($this->rootEmpresa($dto->empresaId)->value(), '/') . '/documentos/geral/',
        });
    }

    public function forUpload(
        DocumentoContextoEnum $contexto,
        DocumentoCategoriaEnum $categoria,
        int $entidadeId,
        string $extension,
        ?int $empresaId = null,
    ): S3Path {
        $uuid = (string) Str::uuid();

        return new S3Path(match ($contexto) {
            DocumentoContextoEnum::EMPRESA, DocumentoContextoEnum::GERAL => $this->empresaDocumentoPath($entidadeId, $categoria, $uuid, $extension),
            DocumentoContextoEnum::PROCESSO => $this->processoPath($entidadeId, $categoria, $uuid, $extension),
            DocumentoContextoEnum::LICENCIAMENTO => $this->licenciamentoPath($entidadeId, $categoria, $uuid, $extension),
            DocumentoContextoEnum::CUSTOMER => $this->customerPath($entidadeId, $categoria, $uuid, $extension, $empresaId),
            default => throw new \InvalidArgumentException('Contexto de upload não suportado.'),
        });
    }

    public function forFolderUpload(Empresa|int $empresa, ArquivoPasta $folder, DocumentoCategoriaEnum $categoria, string $extension): S3Path
    {
        $root = rtrim($this->rootEmpresa($empresa)->value(), '/');
        $folderPath = trim($folder->path !== '' ? $folder->path : 'documentos', '/');
        $uuid = (string) Str::uuid();

        return new S3Path("{$root}/{$folderPath}/{$categoria->value}/" . now()->format('Y/m') . "/{$uuid}.{$extension}");
    }

    private function empresaDocumentoPath(int $empresaId, DocumentoCategoriaEnum $categoria, string $uuid, string $extension): string
    {
        $root = rtrim($this->rootEmpresa($empresaId)->value(), '/');

        return "{$root}/documentos/geral/{$categoria->value}/" . now()->format('Y/m') . "/{$uuid}.{$extension}";
    }

    private function processoPath(int $processoId, DocumentoCategoriaEnum $categoria, string $uuid, string $extension): string
    {
        $processo = Processo::query()->findOrFail($processoId);
        $root = rtrim($this->rootEmpresa((int) $processo->empresa_id)->value(), '/');

        return "{$root}/processos/{$processo->id}/{$categoria->value}/{$uuid}.{$extension}";
    }

    private function licenciamentoPath(int $licenciamentoId, DocumentoCategoriaEnum $categoria, string $uuid, string $extension): string
    {
        $licenciamento = Licenciamento::query()->findOrFail($licenciamentoId);
        $createdAt = $licenciamento->created_at ?? now();
        $root = rtrim($this->rootEmpresa((int) $licenciamento->empresa_id)->value(), '/');

        return "{$root}/licenciamentos/{$createdAt->format('Y/m')}/{$licenciamento->id}/{$categoria->value}/{$uuid}.{$extension}";
    }

    private function customerPath(int $customerId, DocumentoCategoriaEnum $categoria, string $uuid, string $extension, ?int $empresaId = null): string
    {
        $customer = Customer::query()->findOrFail($customerId);
        $resolvedEmpresaId = $empresaId ?: (int) ($customer->empresa_id ?: $customer->empresas()->value('empresas.id'));
        $root = rtrim($this->rootEmpresa($resolvedEmpresaId)->value(), '/');

        return "{$root}/clientes/{$customer->id}/geral/{$categoria->value}/{$uuid}.{$extension}";
    }

    private function empresaKey(Empresa $empresa): string
    {
        $candidate = filled($empresa->conta) ? (string) $empresa->conta : (string) $empresa->Sigla;
        $slug = Str::slug($candidate);

        if ($slug !== '') {
            return $slug;
        }

        return substr(hash('sha256', $empresa->getKey() . '|' . config('app.key')), 0, 24);
    }
}
