<?php

namespace App\Domains\Empresa\Actions;

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
        $path = $file->storeAs("empresa/{$empresa->id}/logotipos", $name, 's3');
        $url = Storage::disk('s3')->url($path);

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

        return str_contains($url, 'empresa/') ? ltrim(parse_url($url, PHP_URL_PATH) ?: '', '/') : null;
    }
}
