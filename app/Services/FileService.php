<?php

namespace App\Services;

use Aws\Exception\AwsException;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileService
{
    private string $bucket = 'logigate-arquivos-aduaneiro';

    public function __construct(private readonly ?S3Client $s3Client = null)
    {
    }

    public function listItems(int $empresaId): array
    {
        $result = $this->client()->listObjectsV2([
            'Bucket' => $this->bucket,
            'Prefix' => $this->tenantPrefix($empresaId),
            'Delimiter' => '/',
        ]);

        $folders = $result['CommonPrefixes'] ?? [];
        $files = $result['Contents'] ?? [];

        $items = array_map(function ($folder) {
            return [
                'name' => basename($folder['Prefix']),
                'path' => $folder['Prefix'],
                'type' => 'folder',
            ];
        }, $folders);

        return array_merge($items, array_map(function ($file) {
            return [
                'name' => basename($file['Key']),
                'path' => $file['Key'],
                'size' => $file['Size'],
                'last_modified' => $file['LastModified'],
                'type' => 'file',
            ];
        }, $files));
    }

    public function uploadFiles(int $empresaId, array $files, ?string $pastaRaiz = null): void
    {
        $pastaRaiz = $this->normalizeTenantRelativePath($empresaId, $pastaRaiz);

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $filename = basename($file->getClientOriginalName());
            $relativePath = trim($pastaRaiz . '/' . $filename, '/');
            $filePath = $this->buildTenantKey($empresaId, $relativePath);

            $this->client()->putObject([
                'Bucket' => $this->bucket,
                'Key' => $filePath,
                'SourceFile' => $file->getPathname(),
            ]);
        }
    }

    public function bulkAction(int $empresaId, array $files, string $action, ?string $destinationFolder = null): void
    {
        foreach ($files as $fileKey) {
            $normalizedSource = $this->normalizeTenantKey($empresaId, $fileKey);

            if ($action === 'delete') {
                Storage::disk('s3')->delete($normalizedSource);
                continue;
            }

            $fileContent = Storage::disk('s3')->get($normalizedSource);
            $newKey = $this->buildTenantKey(
                $empresaId,
                trim($this->normalizeTenantRelativePath($empresaId, $destinationFolder) . '/' . basename($normalizedSource), '/')
            );
            Storage::disk('s3')->put($newKey, $fileContent);

            if ($action === 'move') {
                Storage::disk('s3')->delete($normalizedSource);
            }
        }
    }

    public function listFolderContents(int $empresaId, string $arquivo): array
    {
        $prefix = $this->buildTenantKey($empresaId, $this->normalizeTenantRelativePath($empresaId, $arquivo), true);
        $result = $this->client()->listObjectsV2([
            'Bucket' => $this->bucket,
            'Prefix' => $prefix,
        ]);

        return $result->get('Contents') ?? [];
    }

    public function createMasterFolder(int $empresaId): void
    {
        $this->client()->putObject([
            'Bucket' => $this->bucket,
            'Key' => $this->tenantPrefix($empresaId),
            'Body' => '',
        ]);
    }

    public function createFolder(int $empresaId, string $nomePasta, string $pastaRaiz): void
    {
        $caminhoCompleto = $this->buildTenantKey(
            $empresaId,
            trim($this->normalizeTenantRelativePath($empresaId, $pastaRaiz) . '/' . trim($nomePasta, '/'), '/'),
            true
        );

        $this->client()->putObject([
            'Bucket' => $this->bucket,
            'Key' => $caminhoCompleto,
            'Body' => '',
        ]);
    }

    public function deleteObject(int $empresaId, string $arquivo): void
    {
        $this->client()->deleteObject([
            'Bucket' => $this->bucket,
            'Key' => $this->normalizeTenantKey($empresaId, $arquivo),
        ]);
    }

    public function downloadObject(int $empresaId, string $key): array
    {
        $normalizedKey = $this->normalizeTenantKey($empresaId, $key);
        $result = $this->client()->getObject([
            'Bucket' => $this->bucket,
            'Key' => $normalizedKey,
        ]);

        return [
            'body' => $result['Body'],
            'content_type' => $result['ContentType'],
            'filename' => basename($normalizedKey),
        ];
    }

    public function createPreviewUrl(int $empresaId, string $key): string
    {
        $normalizedKey = $this->normalizeTenantKey($empresaId, $key);
        $command = $this->client()->getCommand('GetObject', [
            'Bucket' => $this->bucket,
            'Key' => $normalizedKey,
        ]);

        return (string) $this->client()->createPresignedRequest($command, '+20 minutes')->getUri();
    }

    public function tenantPrefix(int $empresaId): string
    {
        return 'empresa/' . $empresaId . '/files/';
    }

    private function normalizeTenantRelativePath(int $empresaId, ?string $path): string
    {
        $normalized = trim((string) $path);
        $normalized = urldecode($normalized);
        $normalized = str_replace('\\', '/', $normalized);
        $normalized = preg_replace('#/+#', '/', $normalized);
        $normalized = ltrim($normalized, '/');

        $tenantPrefix = 'empresa/' . $empresaId . '/files/';
        if (Str::startsWith($normalized, $tenantPrefix)) {
            $normalized = substr($normalized, strlen($tenantPrefix));
        }

        if (preg_match('#^empresa/(\d+)/files/#', $normalized, $matches) === 1 && (int) $matches[1] !== $empresaId) {
            abort(403, 'Acesso negado ao arquivo.');
        }

        if (preg_match('#^Despachantes/[^/]+/?#', $normalized)) {
            $normalized = preg_replace('#^Despachantes/[^/]+/?#', '', $normalized) ?? '';
        }

        if ($normalized === '' || $normalized === '/') {
            return '';
        }

        $segments = array_filter(explode('/', $normalized), static fn ($segment) => $segment !== '');
        foreach ($segments as $segment) {
            abort_if($segment === '.' || $segment === '..', 403, 'Caminho inválido.');
        }

        return implode('/', $segments);
    }

    private function buildTenantKey(int $empresaId, string $relativePath, bool $asFolder = false): string
    {
        $relativePath = trim($relativePath, '/');
        $key = $this->tenantPrefix($empresaId) . ($relativePath !== '' ? $relativePath : '');

        return $asFolder ? rtrim($key, '/') . '/' : $key;
    }

    private function normalizeTenantKey(int $empresaId, ?string $incomingKey): string
    {
        $relativePath = $this->normalizeTenantRelativePath($empresaId, $incomingKey);
        $normalizedKey = $this->buildTenantKey($empresaId, $relativePath);

        abort_unless(Str::startsWith($normalizedKey, $this->tenantPrefix($empresaId)), 403, 'Acesso negado ao arquivo.');

        return $normalizedKey;
    }

    private function client(): S3Client
    {
        return $this->s3Client ?? new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);
    }
}
