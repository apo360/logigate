<?php

namespace App\Domains\Exportadores\Actions;

use App\Domains\Exportadores\Repositories\ExportadorRepositoryInterface;
use App\Models\Empresa;
use App\Models\Exportador;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class DeleteExportadorAction
{
    public function __construct(
        private readonly ExportadorRepositoryInterface $exportadores,
    ) {
    }

    public function execute(Exportador $exportador, Empresa $empresa, User $user): void
    {
        DB::transaction(function () use ($exportador, $empresa, $user): void {
            if ($this->exportadores->hasEmpresaAssociation($exportador, $empresa)) {
                $this->exportadores->detachFromEmpresa($exportador, $empresa);
            }

            if (! $this->canHardDelete($user, $exportador)) {
                return;
            }

            $exportador->delete();
        });
    }

    private function canHardDelete(User $user, Exportador $exportador): bool
    {
        return ($user->hasRole('admin') || $user->can('delete-global-exportador'))
            && ! $this->exportadores->hasBusinessDependencies($exportador);
    }
}
