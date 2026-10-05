<?php

namespace App\Domains\Exportador\Actions;

use App\Domains\Exportador\Data\ExportadorFormData;
use App\Models\Empresa;
use App\Models\Exportador;
use App\Models\User;

/** Legacy adapter to the canonical Exportadores domain. */
class CreateExportadorAction
{
    public function __construct(private readonly \App\Domains\Exportadores\Actions\CreateOrAssociateExportadorAction $create) {}

    public function execute(ExportadorFormData $formData, Empresa $empresa, User $user, ?Exportador $exportador = null): Exportador
    {
        if ($exportador !== null) {
            throw \Illuminate\Validation\ValidationException::withMessages(['Exportador' => 'Use a ação de atualização para editar um exportador existente.']);
        }
        $data = \App\Domains\Exportadores\Data\ExportadorFormData::fromArray(get_object_vars($formData));
        return $this->create->execute($data, $empresa, $user);
    }
}
