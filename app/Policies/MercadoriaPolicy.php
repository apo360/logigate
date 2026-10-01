<?php

namespace App\Policies;

use App\Application\Mercadoria\Services\MercadoriaTenantAccessService;
use App\Models\Mercadoria;
use App\Models\Processo;
use App\Models\Licenciamento;
use App\Models\User;
use App\Support\BusinessAuthorization;

class MercadoriaPolicy
{
    public function __construct(private readonly MercadoriaTenantAccessService $access)
    {
    }

    public function view(User $user, Mercadoria $mercadoria): bool
    {
        return $this->access->belongsToActiveEmpresa($user, $mercadoria)
            && BusinessAuthorization::allows($user, 'mercadorias.view');
    }

    public function viewAny(User $user): bool
    {
        return BusinessAuthorization::allows($user, 'mercadorias.view');
    }

    public function create(User $user, Processo|Licenciamento|null $parent = null): bool
    {
        $id = \App\Support\TenantContext::empresaId($user);
        return $id !== null && $parent !== null && (int) $parent->empresa_id === $id
            && BusinessAuthorization::allows($user, 'mercadorias.create');
    }

    public function update(User $user, Mercadoria $mercadoria): bool
    {
        return $this->access->belongsToActiveEmpresa($user, $mercadoria)
            && BusinessAuthorization::allows($user, 'mercadorias.update');
    }

    public function delete(User $user, Mercadoria $mercadoria): bool
    {
        return $this->access->belongsToActiveEmpresa($user, $mercadoria)
            && BusinessAuthorization::allows($user, 'mercadorias.delete');
    }
}
