<?php

namespace App\Application\Arquivo\Policies;

use App\Models\DocumentoArquivo;
use App\Models\Empresa;
use App\Models\ClientePortal;
use App\Models\Customer;
use App\Models\User;

final class DocumentoPolicy
{
    public function viewAny(User $user, Empresa $empresa): bool
    {
        return $this->canUseArquivo($user, $empresa, ['arquivo.view', 'arquivo.manage']);
    }

    public function upload(User $user, Empresa $empresa): bool
    {
        return $this->canUseArquivo($user, $empresa, ['arquivo.upload', 'arquivo.manage']);
    }

    public function view(User $user, DocumentoArquivo $documento): bool
    {
        return $this->sameEmpresa($user, $documento)
            && $this->hasAnyArquivoPermission($user, ['arquivo.view', 'arquivo.manage']);
    }

    public function download(User $user, DocumentoArquivo $documento): bool
    {
        return $this->sameEmpresa($user, $documento)
            && $this->hasAnyArquivoPermission($user, ['arquivo.download', 'arquivo.manage']);
    }

    public function delete(User $user, DocumentoArquivo $documento): bool
    {
        return $this->sameEmpresa($user, $documento)
            && $this->hasAnyArquivoPermission($user, ['arquivo.delete', 'arquivo.manage']);
    }

    public function manage(User $user, Empresa $empresa): bool
    {
        return $this->canUseArquivo($user, $empresa, ['arquivo.manage']);
    }

    public function viewPortal(ClientePortal $portal, DocumentoArquivo $documento): bool
    {
        return $this->portalCanAccess($portal, $documento);
    }

    public function downloadPortal(ClientePortal $portal, DocumentoArquivo $documento): bool
    {
        return $this->portalCanAccess($portal, $documento);
    }

    public function uploadPortal(ClientePortal $portal, Customer $customer): bool
    {
        if (! $portal->is_active || ! $portal->customer_id || ! $portal->empresa_id) {
            return false;
        }

        if ((int) $portal->customer_id !== (int) $customer->id) {
            return false;
        }

        return (int) ($customer->empresa_id ?? 0) === (int) $portal->empresa_id
            || $customer->empresas()->where('empresas.id', $portal->empresa_id)->exists();
    }

    private function sameEmpresa(User $user, DocumentoArquivo $documento): bool
    {
        return $user->empresas()->where('empresas.id', $documento->empresa_id)->exists();
    }

    private function canUseArquivo(User $user, Empresa $empresa, array $permissions): bool
    {
        return $user->empresas()->where('empresas.id', $empresa->id)->exists()
            && $this->hasAnyArquivoPermission($user, $permissions);
    }

    private function hasAnyArquivoPermission(User $user, array $permissions): bool
    {
        return $user->getAllPermissions()
            ->pluck('name')
            ->intersect($permissions)
            ->isNotEmpty();
    }

    private function portalCanAccess(ClientePortal $portal, DocumentoArquivo $documento): bool
    {
        if (! $portal->is_active || ! $portal->customer_id || ! $portal->empresa_id) {
            return false;
        }

        if ((int) $documento->empresa_id !== (int) $portal->empresa_id) {
            return false;
        }

        if ((int) $documento->customer_id !== (int) $portal->customer_id) {
            return false;
        }

        if ($documento->documentable_type !== Customer::class || (int) $documento->documentable_id !== (int) $portal->customer_id) {
            return false;
        }

        return in_array($documento->visibilidade, ['cliente', 'portal', 'publico'], true)
            || (bool) data_get($documento->metadata, 'portal_visible')
            || data_get($documento->metadata, 'uploaded_from') === 'cliente_portal';
    }
}
