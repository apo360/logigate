<?php

namespace App\Application\Arquivo\Actions;

use App\Models\ArquivoPasta;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class CriarArquivoPastaAction
{
    public function execute(Empresa $empresa, User $user, string $name, ?int $parentId = null, string $type = 'custom'): ArquivoPasta
    {
        if (! Schema::hasTable('arquivo_pastas')) {
            throw new InvalidArgumentException('A tabela arquivo_pastas ainda não existe. Execute a migration aprovada.');
        }

        $name = trim($name);
        if ($name === '') {
            throw new InvalidArgumentException('Informe o nome da pasta.');
        }

        if (str_contains($name, '../') || str_contains($name, '..\\') || preg_match('/[\\\\:*?"<>|]/', $name)) {
            throw new InvalidArgumentException('O nome da pasta contém caracteres não permitidos.');
        }

        $parent = $this->resolveParent($empresa, $parentId);
        $slug = Str::slug($name);

        if ($slug === '' || $slug === '.' || $slug === '..') {
            throw new InvalidArgumentException('Informe um nome de pasta válido.');
        }

        $path = $this->buildPath($parent, $slug);

        $duplicated = ArquivoPasta::query()
            ->where('empresa_id', $empresa->id)
            ->where('parent_id', $parent?->id)
            ->where('slug', $slug)
            ->exists();

        if ($duplicated) {
            throw new InvalidArgumentException('Já existe uma pasta com este nome nesta localização.');
        }

        return ArquivoPasta::query()->create([
            'uuid' => (string) Str::uuid(),
            'empresa_id' => (int) $empresa->id,
            'parent_id' => $parent?->id,
            'name' => $name,
            'slug' => $slug,
            'path' => $path,
            'type' => $type !== '' ? $type : 'custom',
            'is_system' => false,
            'is_locked' => false,
            'created_by' => (int) $user->id,
            'metadata' => ['created_from' => 'arquivo_index'],
        ]);
    }

    private function resolveParent(Empresa $empresa, ?int $parentId): ?ArquivoPasta
    {
        if (! $parentId) {
            return ArquivoPasta::query()
                ->where('empresa_id', $empresa->id)
                ->where('path', '')
                ->first();
        }

        return ArquivoPasta::query()
            ->where('empresa_id', $empresa->id)
            ->whereKey($parentId)
            ->firstOrFail();
    }

    private function buildPath(?ArquivoPasta $parent, string $slug): string
    {
        $base = trim((string) $parent?->path, '/');

        return $base === '' ? $slug : "{$base}/{$slug}";
    }
}
