<?php

namespace App\Application\Arquivo\Services;

use App\Domains\Arquivo\ValueObjects\S3Path;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class FileStorageService
{
    public function disk(): string
    {
        return 's3';
    }

    public function bucket(): ?string
    {
        return config('filesystems.disks.s3.bucket');
    }

    public function put(S3Path $path, UploadedFile $file): void
    {
        Storage::disk($this->disk())->put($path->value(), fopen($file->getRealPath(), 'rb'), [
            'visibility' => 'private',
        ]);
    }

    public function createDirectory(S3Path $path): void
    {
        Storage::disk($this->disk())->put(rtrim($path->value(), '/') . '/', '');
    }

    public function temporaryUrl(S3Path $path, int $minutes = 5): string
    {
        return Storage::disk($this->disk())->temporaryUrl($path->value(), now()->addMinutes($minutes));
    }
}
