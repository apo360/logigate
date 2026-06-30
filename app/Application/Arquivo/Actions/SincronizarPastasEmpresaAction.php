<?php

namespace App\Application\Arquivo\Actions;

use App\Models\ArquivoPasta;
use App\Models\Empresa;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

final class SincronizarPastasEmpresaAction
{
    private const DEFAULT_FOLDERS = [
        ['name' => 'Raiz', 'slug' => 'raiz', 'path' => '', 'type' => 'raiz'],
        ['name' => 'Documentos Gerais', 'slug' => 'documentos', 'path' => 'documentos', 'type' => 'geral'],
        ['name' => 'Processos', 'slug' => 'processos', 'path' => 'processos', 'type' => 'processo'],
        ['name' => 'Licenciamentos', 'slug' => 'licenciamentos', 'path' => 'licenciamentos', 'type' => 'licenciamento'],
        ['name' => 'Clientes', 'slug' => 'clientes', 'path' => 'clientes', 'type' => 'cliente'],
        ['name' => 'Mercadorias', 'slug' => 'mercadorias', 'path' => 'mercadorias', 'type' => 'mercadoria'],
        ['name' => 'Usuários', 'slug' => 'usuarios', 'path' => 'usuarios', 'type' => 'usuario'],
        ['name' => 'Temporários', 'slug' => 'temporarios', 'path' => 'temporarios', 'type' => 'temporario'],
        ['name' => 'Exportações', 'slug' => 'exportacoes', 'path' => 'exportacoes', 'type' => 'exportacao'],
        ['name' => 'Auditoria', 'slug' => 'auditoria', 'path' => 'auditoria', 'type' => 'auditoria'],
    ];

    public function execute(Empresa|int $empresa): void
    {
        if (! Schema::hasTable('arquivo_pastas')) {
            return;
        }

        $empresaModel = $empresa instanceof Empresa ? $empresa : Empresa::query()->findOrFail($empresa);
        $root = null;

        foreach (self::DEFAULT_FOLDERS as $folder) {
            $pasta = ArquivoPasta::query()->firstOrCreate(
                [
                    'empresa_id' => (int) $empresaModel->id,
                    'path' => $folder['path'],
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'parent_id' => $folder['path'] === '' ? null : $root?->id,
                    'name' => $folder['name'],
                    'slug' => $folder['slug'],
                    'type' => $folder['type'],
                    'is_system' => true,
                    'is_locked' => true,
                    'metadata' => ['created_by_system' => true],
                ],
            );

            if ($folder['path'] === '') {
                $root = $pasta;
            }
        }
    }

    public static function defaultFolderPaths(): array
    {
        return array_column(self::DEFAULT_FOLDERS, 'path');
    }
}
