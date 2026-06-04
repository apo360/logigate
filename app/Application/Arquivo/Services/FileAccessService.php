<?php

namespace App\Application\Arquivo\Services;

use App\Domains\Arquivo\Enums\DocumentoCategoriaEnum;
use App\Domains\Arquivo\Exceptions\AcessoDocumentoNegadoException;
use App\Domains\Arquivo\Exceptions\UploadDocumentoInvalidoException;
use App\Models\DocumentoArquivo;
use App\Models\User;
use Illuminate\Http\UploadedFile;

final class FileAccessService
{
    private const MAX_SIZE_BY_CATEGORY = [
        'xml' => 2 * 1024 * 1024,
        'txt' => 2 * 1024 * 1024,
        'relatorios' => 20 * 1024 * 1024,
    ];

    private const DEFAULT_MAX_SIZE = 10 * 1024 * 1024;

    private const ALLOWED_EXTENSIONS = [
        'documentos' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
        'mercadorias' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
        'despesas' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
        'comprovativos' => ['pdf', 'jpg', 'jpeg', 'png'],
        'relatorios' => ['pdf'],
        'xml' => ['xml'],
        'txt' => ['txt'],
        'proformas' => ['pdf'],
        'recibos' => ['pdf'],
        'comprovativos_pagamento' => ['pdf', 'jpg', 'jpeg', 'png'],
        'documentos_identificacao' => ['pdf', 'jpg', 'jpeg', 'png'],
        'contratos' => ['pdf', 'doc', 'docx'],
        'outros' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'],
    ];

    public function assertUserCanAccessEmpresa(User $user, int $empresaId): void
    {
        $allowed = $user->empresas()->where('empresas.id', $empresaId)->exists();

        if (! $allowed) {
            throw new AcessoDocumentoNegadoException('Sem permissão para acessar documentos desta empresa.');
        }
    }

    public function assertCanView(User $user, DocumentoArquivo $documento): void
    {
        $this->assertUserCanAccessEmpresa($user, (int) $documento->empresa_id);
    }

    public function assertValidUpload(UploadedFile $file, DocumentoCategoriaEnum $categoria): void
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $allowed = self::ALLOWED_EXTENSIONS[$categoria->value] ?? self::ALLOWED_EXTENSIONS['outros'];

        if (! in_array($extension, $allowed, true)) {
            throw new UploadDocumentoInvalidoException('Extensão de documento não permitida para esta categoria.');
        }

        $maxSize = self::MAX_SIZE_BY_CATEGORY[$categoria->value] ?? self::DEFAULT_MAX_SIZE;
        if ((int) $file->getSize() > $maxSize) {
            throw new UploadDocumentoInvalidoException('Documento excede o tamanho máximo permitido.');
        }
    }
}
