<?php

namespace App\Domains\Empresa\Actions;

use App\Application\Arquivo\Services\FileStorageService;
use App\Application\Arquivo\Services\S3PathBuilder;
use App\Domains\Empresa\Repositories\EmpresaRepositoryInterface;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class AtualizarLogotipoEmpresaAction
{
    public function __construct(
        private readonly EmpresaRepositoryInterface $empresas,
        private readonly S3PathBuilder $pathBuilder,
        private readonly FileStorageService $storage,
    ) {
    }

    public function execute(User $actor, Empresa $empresa, UploadedFile $file): Empresa
    {
        Gate::forUser($actor)->authorize('update', $empresa);

        $oldPath = $this->pathFromUrl((string) $empresa->Logotipo);

        if ($oldPath) {
            Storage::disk('s3')->delete($oldPath);
        }

        $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $this->pathBuilder->empresaLogotipo($empresa, $name);
        $this->storage->put($path, $file);
        $url = Storage::disk($this->storage->disk())->url($path->value());

        return $this->empresas->update($empresa, ['Logotipo' => $url]);
    }

    private function pathFromUrl(string $url): ?string
    {
        if ($url === '') {
            return null;
        }

        $base = (string) config('filesystems.disks.s3.url');

        if ($base !== '' && str_starts_with($url, $base)) {
            return ltrim(substr($url, strlen($base)), '/');
        }

        $path = ltrim(parse_url($url, PHP_URL_PATH) ?: '', '/');

        return str_contains($path, 'empresa/') || str_contains($path, 'despachantes/') || str_contains($path, 'logigate/empresas/')
            ? $path
            : null;
    }
}
