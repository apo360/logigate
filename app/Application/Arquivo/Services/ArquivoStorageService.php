<?php

namespace App\Application\Arquivo\Services;

use App\Application\Arquivo\DTOs\StorageStatusDTO;
use App\Domains\Arquivo\ValueObjects\S3Path;
use App\Models\Empresa;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ArquivoStorageService
{
    public function disk(): string
    {
        return 's3';
    }

    public function bucket(): ?string
    {
        return config('filesystems.disks.s3.bucket');
    }

    public function region(): ?string
    {
        return config('filesystems.disks.s3.region');
    }

    public function isConfigured(): bool
    {
        $disk = config('filesystems.disks.s3', []);

        return ($disk['driver'] ?? null) === 's3'
            && filled($disk['key'] ?? null)
            && filled($disk['secret'] ?? null)
            && filled($disk['region'] ?? null)
            && filled($disk['bucket'] ?? null);
    }

    public function maskedBucket(): ?string
    {
        $bucket = (string) $this->bucket();

        if ($bucket === '') {
            return null;
        }

        if (strlen($bucket) <= 6) {
            return substr($bucket, 0, 1) . '***' . substr($bucket, -1);
        }

        return substr($bucket, 0, 3) . '***' . substr($bucket, -3);
    }

    public function empresaRootPath(Empresa $empresa): S3Path
    {
        return app(S3PathBuilder::class)->rootEmpresa($empresa);
    }

    public function defaultFolderPaths(Empresa $empresa): array
    {
        $root = rtrim($this->empresaRootPath($empresa)->value(), '/');

        return collect([
            'raiz',
            'documentos',
            'processos',
            'licenciamentos',
            'clientes',
            'mercadorias',
            'usuarios',
            'temporarios',
            'exportacoes',
            'auditoria',
        ])->map(fn (string $folder): S3Path => new S3Path("{$root}/{$folder}/"))->all();
    }

    public function checkConnection(?Empresa $empresa = null): StorageStatusDTO
    {
        $root = $empresa ? $this->empresaRootPath($empresa)->value() : null;

        if (! $this->isConfigured()) {
            return new StorageStatusDTO(
                configured: false,
                connected: false,
                rootExists: false,
                disk: $this->disk(),
                bucket: $this->maskedBucket(),
                region: $this->region(),
                rootPath: $root,
                checkedAt: now()->format('d/m/Y H:i'),
                message: 'S3 não configurado. Defina credenciais, região e bucket no ambiente.',
            );
        }

        try {
            $rootExists = $root ? Storage::disk($this->disk())->exists($root) : false;

            return new StorageStatusDTO(
                configured: true,
                connected: true,
                rootExists: $rootExists,
                disk: $this->disk(),
                bucket: $this->maskedBucket(),
                region: $this->region(),
                rootPath: $root,
                checkedAt: now()->format('d/m/Y H:i'),
                message: $rootExists ? 'Ligação verificada.' : 'Ligação verificada; pasta raiz ainda não materializada.',
            );
        } catch (\Throwable $exception) {
            Log::warning('ARQUIVO_S3_CONNECTION_FAILED', [
                'empresa_id' => $empresa?->id,
                'error' => $exception->getMessage(),
            ]);

            return new StorageStatusDTO(
                configured: true,
                connected: false,
                rootExists: false,
                disk: $this->disk(),
                bucket: $this->maskedBucket(),
                region: $this->region(),
                rootPath: $root,
                checkedAt: now()->format('d/m/Y H:i'),
                message: 'Falha ao verificar ligação S3: ' . $exception->getMessage(),
            );
        }
    }

    public function configurationStatus(?Empresa $empresa = null): StorageStatusDTO
    {
        $configured = $this->isConfigured();

        return new StorageStatusDTO(
            configured: $configured,
            connected: false,
            rootExists: false,
            disk: $this->disk(),
            bucket: $this->maskedBucket(),
            region: $this->region(),
            rootPath: $empresa ? $this->empresaRootPath($empresa)->value() : null,
            checkedAt: null,
            message: $configured
                ? 'S3 configurado. Use verificar ligação para validar acesso ao bucket.'
                : 'S3 não configurado. Defina credenciais, região e bucket no ambiente.',
        );
    }

    public function ensureEmpresaRoot(Empresa $empresa): void
    {
        $this->ensureConfigured();
        $this->createDirectory($this->empresaRootPath($empresa));
    }

    public function ensureDefaultFolders(Empresa $empresa): void
    {
        $this->ensureEmpresaRoot($empresa);

        foreach ($this->defaultFolderPaths($empresa) as $path) {
            $this->createDirectory($path);
        }
    }

    public function put(S3Path $path, UploadedFile $file): void
    {
        $this->ensureConfigured();

        $stored = Storage::disk($this->disk())->put($path->value(), fopen($file->getRealPath(), 'rb'), [
            'visibility' => 'private',
        ]);

        if (! $stored) {
            throw new \RuntimeException('Falha ao gravar documento no S3 privado.');
        }
    }

    public function createDirectory(S3Path $path): void
    {
        $this->ensureConfigured();

        $stored = Storage::disk($this->disk())->put(rtrim($path->value(), '/') . '/', '', [
            'visibility' => 'private',
        ]);

        if (! $stored) {
            throw new \RuntimeException('Falha ao criar pasta lógica no S3 privado.');
        }
    }

    public function temporaryUrl(S3Path $path, int $minutes = 5): string
    {
        $this->ensureConfigured();

        return Storage::disk($this->disk())->temporaryUrl($path->value(), now()->addMinutes($minutes));
    }

    public function assertPathBelongsToEmpresa(Empresa $empresa, string $path): void
    {
        $root = $this->empresaRootPath($empresa)->value();

        abort_unless(str_starts_with($path, $root), 403, 'Acesso negado ao arquivo desta empresa.');
    }

    private function ensureConfigured(): void
    {
        if (! $this->isConfigured()) {
            throw new \RuntimeException('S3 não configurado para Gestão Documental.');
        }
    }
}
